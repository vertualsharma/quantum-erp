<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\ExamClassSetup;
use App\Models\ExamSubjectMarkSetup;
use App\Models\Student;
use App\Models\StudentExamAttendance;
use App\Models\StudentResult;
use App\Models\StudentSubjectMark;
use App\Models\StudentSubjectResult;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
    protected static array $gradeScale = [
        ['min' => 90, 'grade' => 'A+', 'point' => 4.0],
        ['min' => 80, 'grade' => 'A',  'point' => 3.6],
        ['min' => 70, 'grade' => 'B+', 'point' => 3.2],
        ['min' => 60, 'grade' => 'B',  'point' => 2.8],
        ['min' => 50, 'grade' => 'C+', 'point' => 2.4],
        ['min' => 40, 'grade' => 'C',  'point' => 2.0],
        ['min' => 35, 'grade' => 'D',  'point' => 1.6],
        ['min' => 0,  'grade' => 'NG', 'point' => 0.0],
    ];

    protected function gradeFor(float $percent): array
    {
        foreach (self::$gradeScale as $row) {
            if ($percent >= $row['min']) {
                return [$row['grade'], $row['point']];
            }
        }

        return ['NG', 0.0];
    }

    protected function theoryGrade(float $percent): array
    {
        if ($percent < 35) {
            return ['NG', 0.0];
        }

        return $this->gradeFor($percent);
    }

    protected function practicalGrade(float $percent): array
    {
        if ($percent < 40) {
            return ['NG', 0.0];
        }

        return $this->gradeFor($percent);
    }

    protected function finalGrade(string $thGrade, float $thPoint, ?string $inGrade, ?float $inPoint): string
    {
        if ($inGrade === null) {
            return $thGrade;
        }

        return $thPoint <= $inPoint ? $thGrade : $inGrade;
    }

    public function calculateForStudent(int $examClassSetupId, int $studentId): StudentResult
    {
        return DB::transaction(function () use ($examClassSetupId, $studentId) {
            $examClassSetup = ExamClassSetup::findOrFail($examClassSetupId);
            $student = Student::findOrFail($studentId);

            $classSubjects = ClassSubject::with('subject')
                ->where('school_class_id', $examClassSetup->school_class_id)
                ->where('is_active', true)
                ->get();

            $studentResult = StudentResult::updateOrCreate(
                ['exam_class_setup_id' => $examClassSetupId, 'student_id' => $studentId],
                []
            );

            $totalWeightedPoints = 0.0;
            $totalCreditHours = 0.0;
            $anySubjectFailed = false;
            $anySubjectCalculated = false;

            foreach ($classSubjects as $classSubject) {
                $markSetup = ExamSubjectMarkSetup::where('exam_class_setup_id', $examClassSetupId)
                    ->where('subject_id', $classSubject->subject_id)
                    ->first();

                if (! $markSetup) {
                    continue;
                }

                $mark = StudentSubjectMark::where('exam_subject_mark_setup_id', $markSetup->id)
                    ->where('student_id', $studentId)
                    ->first();

                $isGpaCounted = $classSubject->grading_type === 'Regular';

                $thPercent = null;
                $thGrade = null;
                $thPoint = null;
                $inPercent = null;
                $inGrade = null;
                $inPoint = null;

                if ($markSetup->theory_full_marks > 0) {
                    $anySubjectCalculated = true;

                    if (! $mark || $mark->theory_absent) {
                        $thPercent = 0;
                        [$thGrade, $thPoint] = ['NG', 0.0];
                    } else {
                        $thPercent = round(($mark->theory_obtained / $markSetup->theory_full_marks) * 100, 2);
                        [$thGrade, $thPoint] = $this->theoryGrade($thPercent);
                    }
                }

                if ($markSetup->practical_full_marks > 0) {
                    $anySubjectCalculated = true;

                    if (! $mark || $mark->practical_absent) {
                        $inPercent = 0;
                        [$inGrade, $inPoint] = ['NG', 0.0];
                    } else {
                        $inPercent = round(($mark->practical_obtained / $markSetup->practical_full_marks) * 100, 2);
                        [$inGrade, $inPoint] = $this->practicalGrade($inPercent);
                    }
                }

                $subjectFinalGrade = $thGrade !== null
                    ? $this->finalGrade($thGrade, $thPoint, $inGrade, $inPoint)
                    : $inGrade;

                $subjectFailed = ($thGrade === 'NG') || ($inGrade === 'NG');

                if ($isGpaCounted && $subjectFailed) {
                    $anySubjectFailed = true;
                }

                if ($isGpaCounted) {
                    if ($thGrade !== null) {
                        $totalWeightedPoints += $thPoint * (float) $classSubject->th_credit_hour;
                        $totalCreditHours += (float) $classSubject->th_credit_hour;
                    }

                    if ($inGrade !== null) {
                        $totalWeightedPoints += $inPoint * (float) $classSubject->pr_credit_hour;
                        $totalCreditHours += (float) $classSubject->pr_credit_hour;
                    }
                }

                StudentSubjectResult::updateOrCreate(
                    ['student_result_id' => $studentResult->id, 'subject_id' => $classSubject->subject_id],
                    [
                        'theory_percent' => $thPercent,
                        'theory_grade' => $thGrade,
                        'theory_point' => $thPoint,
                        'theory_credit_hour' => $thGrade !== null ? $classSubject->th_credit_hour : null,
                        'practical_percent' => $inPercent,
                        'practical_grade' => $inGrade,
                        'practical_point' => $inPoint,
                        'practical_credit_hour' => $inGrade !== null ? $classSubject->pr_credit_hour : null,
                        'final_grade' => $subjectFinalGrade,
                        'is_gpa_counted' => $isGpaCounted,
                        'remarks' => $this->remarkFor($subjectFinalGrade),
                    ]
                );
            }

            $gpa = $totalCreditHours > 0 ? round($totalWeightedPoints / $totalCreditHours, 2) : null;

            $attendance = StudentExamAttendance::where('exam_class_setup_id', $examClassSetupId)
                ->where('student_id', $studentId)
                ->first();

            $result = ! $anySubjectCalculated
                ? 'INCOMPLETE'
                : ($anySubjectFailed ? 'FAIL' : 'PASS');

            $studentResult->update([
                'gpa' => $gpa,
                'total_credit_hour' => $totalCreditHours,
                'result' => $result,
                'attendance_present' => $attendance?->days_present,
                'attendance_total' => $examClassSetup->total_attendance_days,
                'calculated_at' => now(),
            ]);

            return $studentResult->fresh('subjectResults');
        });
    }

    public function calculateForClass(int $examClassSetupId): void
    {
        $examClassSetup = ExamClassSetup::findOrFail($examClassSetupId);

        $studentIds = Student::where('class_id', $examClassSetup->school_class_id)->pluck('id');

        foreach ($studentIds as $studentId) {
            $this->calculateForStudent($examClassSetupId, $studentId);
        }

        $this->assignRanks($examClassSetupId);
    }

    protected function assignRanks(int $examClassSetupId): void
    {
        $results = StudentResult::where('exam_class_setup_id', $examClassSetupId)
            ->where('result', 'PASS')
            ->orderByDesc('gpa')
            ->get();

        $rank = 0;
        $previousGpa = null;
        $position = 0;

        foreach ($results as $result) {
            $position++;

            if ($previousGpa === null || $result->gpa < $previousGpa) {
                $rank = $position;
            }

            $result->update(['rank' => $rank]);
            $previousGpa = $result->gpa;
        }
    }

    protected function remarkFor(?string $grade): ?string
    {
        return match ($grade) {
            'A+' => 'Outstanding',
            'A' => 'Excellent',
            'B+' => 'Very Good',
            'B' => 'Good',
            'C+' => 'Satisfactory',
            'C' => 'Acceptable',
            'D' => 'Basic',
            'NG' => 'Not Graded',
            default => null,
        };
    }
}