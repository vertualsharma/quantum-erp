<?php

namespace App\Services;

use App\Models\ExamClassSetup;
use App\Models\ExamDerivation;
use App\Models\ExamSubjectMarkSetup;
use App\Models\Student;
use App\Models\StudentExamAttendance;
use App\Models\StudentSubjectMark;

class DerivationService
{
    /**
     * Derive (copy/combine) marks and attendance from all active source
     * exams into the target exam, for every student in the class,
     * weighted by percentage.
     */
    public function deriveForClass(int $targetExamClassSetupId): void
    {
        $derivations = ExamDerivation::where('target_exam_class_setup_id', $targetExamClassSetupId)
            ->where('is_active', true)
            ->with('subjectDerivations')
            ->get();

        if ($derivations->isEmpty()) {
            return;
        }

        $this->ensureMarkSetupsExist($targetExamClassSetupId, $derivations);
        $this->deriveAttendance($targetExamClassSetupId, $derivations);

        $targetMarkSetups = ExamSubjectMarkSetup::where('exam_class_setup_id', $targetExamClassSetupId)->get();

        foreach ($targetMarkSetups as $targetMarkSetup) {
            $studentIds = $this->studentIdsForExamClassSetup($targetExamClassSetupId);

            foreach ($studentIds as $studentId) {
                $this->deriveSubjectForStudent($targetMarkSetup, $studentId, $derivations);
            }
        }
    }

    protected function ensureMarkSetupsExist(int $targetExamClassSetupId, $derivations): void
    {
        $subjectIds = collect();

        foreach ($derivations as $derivation) {
            $sourceSubjectIds = ExamSubjectMarkSetup::where('exam_class_setup_id', $derivation->source_exam_class_setup_id)
                ->pluck('subject_id');

            $subjectIds = $subjectIds->merge($sourceSubjectIds);
        }

        $subjectIds = $subjectIds->unique();

        foreach ($subjectIds as $subjectId) {
            $weightedTheoryFull = 0.0;
            $weightedTheoryPass = 0.0;
            $weightedPracticalFull = 0.0;
            $weightedPracticalPass = 0.0;

            foreach ($derivations as $derivation) {
                $sourceMarkSetup = ExamSubjectMarkSetup::where('exam_class_setup_id', $derivation->source_exam_class_setup_id)
                    ->where('subject_id', $subjectId)
                    ->first();

                if (! $sourceMarkSetup) {
                    continue;
                }

                $subjectOverride = $derivation->subjectDerivations->firstWhere('subject_id', $subjectId);
                $weight = $subjectOverride?->percentage ?? $derivation->percentage;
                $weightFraction = $weight / 100;

                $weightedTheoryFull += (float) $sourceMarkSetup->theory_full_marks * $weightFraction;
                $weightedTheoryPass += (float) $sourceMarkSetup->theory_pass_marks * $weightFraction;
                $weightedPracticalFull += (float) $sourceMarkSetup->practical_full_marks * $weightFraction;
                $weightedPracticalPass += (float) $sourceMarkSetup->practical_pass_marks * $weightFraction;
            }

            ExamSubjectMarkSetup::updateOrCreate(
                ['exam_class_setup_id' => $targetExamClassSetupId, 'subject_id' => $subjectId],
                [
                    'theory_full_marks' => round($weightedTheoryFull, 2),
                    'theory_pass_marks' => round($weightedTheoryPass, 2),
                    'practical_full_marks' => round($weightedPracticalFull, 2),
                    'practical_pass_marks' => round($weightedPracticalPass, 2),
                ]
            );
        }
    }

    /**
     * Copy total_attendance_days (class-level) and each student's
     * days_present into the target exam, weighted across sources.
     */
    protected function deriveAttendance(int $targetExamClassSetupId, $derivations): void
    {
        $weightedTotalDays = 0.0;

        foreach ($derivations as $derivation) {
            $sourceExamClassSetup = ExamClassSetup::find($derivation->source_exam_class_setup_id);

            if (! $sourceExamClassSetup || ! $sourceExamClassSetup->total_attendance_days) {
                continue;
            }

            $weightFraction = $derivation->percentage / 100;
            $weightedTotalDays += (float) $sourceExamClassSetup->total_attendance_days * $weightFraction;
        }

        if ($weightedTotalDays > 0) {
            ExamClassSetup::where('id', $targetExamClassSetupId)
                ->update(['total_attendance_days' => round($weightedTotalDays)]);
        }

        $studentIds = $this->studentIdsForExamClassSetup($targetExamClassSetupId);

        foreach ($studentIds as $studentId) {
            $weightedDaysPresent = 0.0;
            $anySourceFound = false;

            foreach ($derivations as $derivation) {
                $sourceAttendance = StudentExamAttendance::where('exam_class_setup_id', $derivation->source_exam_class_setup_id)
                    ->where('student_id', $studentId)
                    ->first();

                if (! $sourceAttendance) {
                    continue;
                }

                $anySourceFound = true;
                $weightFraction = $derivation->percentage / 100;
                $weightedDaysPresent += (float) $sourceAttendance->days_present * $weightFraction;
            }

            if ($anySourceFound) {
                StudentExamAttendance::updateOrCreate(
                    ['exam_class_setup_id' => $targetExamClassSetupId, 'student_id' => $studentId],
                    ['days_present' => round($weightedDaysPresent)]
                );
            }
        }
    }

    protected function studentIdsForExamClassSetup(int $examClassSetupId)
    {
        $examClassSetup = ExamClassSetup::findOrFail($examClassSetupId);

        return Student::where('class_id', $examClassSetup->school_class_id)->pluck('id');
    }

    protected function deriveSubjectForStudent(ExamSubjectMarkSetup $targetMarkSetup, int $studentId, $derivations): void
    {
        $weightedTheory = 0.0;
        $weightedPractical = 0.0;
        $anyAbsentTheory = false;
        $anyAbsentPractical = false;
        $anySourceFound = false;

        foreach ($derivations as $derivation) {
            $sourceMarkSetup = ExamSubjectMarkSetup::where('exam_class_setup_id', $derivation->source_exam_class_setup_id)
                ->where('subject_id', $targetMarkSetup->subject_id)
                ->first();

            if (! $sourceMarkSetup) {
                continue;
            }

            $sourceMark = StudentSubjectMark::where('exam_subject_mark_setup_id', $sourceMarkSetup->id)
                ->where('student_id', $studentId)
                ->first();

            if (! $sourceMark) {
                continue;
            }

            $anySourceFound = true;

            $subjectOverride = $derivation->subjectDerivations->firstWhere('subject_id', $targetMarkSetup->subject_id);
            $weight = $subjectOverride?->percentage ?? $derivation->percentage;
            $weightFraction = $weight / 100;

            if ($sourceMark->theory_absent) {
                $anyAbsentTheory = true;
            } else {
                $weightedTheory += (float) $sourceMark->theory_obtained * $weightFraction;
            }

            if ($sourceMark->practical_absent) {
                $anyAbsentPractical = true;
            } else {
                $weightedPractical += (float) $sourceMark->practical_obtained * $weightFraction;
            }
        }

        if (! $anySourceFound) {
            return;
        }

        StudentSubjectMark::updateOrCreate(
            ['exam_subject_mark_setup_id' => $targetMarkSetup->id, 'student_id' => $studentId],
            [
                'theory_obtained' => $anyAbsentTheory ? null : round($weightedTheory, 2),
                'theory_absent' => $anyAbsentTheory,
                'practical_obtained' => $anyAbsentPractical ? null : round($weightedPractical, 2),
                'practical_absent' => $anyAbsentPractical,
            ]
        );
    }
}