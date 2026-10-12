<?php

namespace App\Services;

use App\Models\SchoolSetting;
use App\Models\StudentResult;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintResultService
{
    private ?SchoolSetting $cachedSetting = null;

    private function getSetting(): ?SchoolSetting
    {
        if ($this->cachedSetting === null) {
            $this->cachedSetting = SchoolSetting::first();
        }

        return $this->cachedSetting;
    }

    /**
     * Convert a file path to a base64 data URI for fast DomPDF rendering.
     */
    private function fileToDataUri(?string $path): ?string
    {
        if (! $path || ! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path);

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    public function generate(int $studentResultId, string $template = 'grade-sheet'): string
    {
        $result = StudentResult::with([
            'student',
            'subjectResults.subject',
            'examClassSetup.exam',
            'examClassSetup.schoolClass',
            'examClassSetup.academicYear',
        ])->findOrFail($studentResultId);

        $data = $this->prepareStudentData($result);

        $html = view("pdf.{$template}", $data)->render();

        return $this->outputPdf($html);
    }

    public function generateForClass(int $examClassSetupId, ?int $sectionId = null, string $template = 'grade-sheet-batch'): string
    {
        $results = StudentResult::with([
            'student',
            'student.section',
            'subjectResults.subject',
            'examClassSetup.exam',
            'examClassSetup.schoolClass',
            'examClassSetup.academicYear',
        ])
            ->where('exam_class_setup_id', $examClassSetupId)
            ->when($sectionId, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('section_id', $sectionId)))
            ->get();

        if ($template === 'class-summary') {
            $setting = $this->getSetting();
            $setup = $results->first()?->examClassSetup;

            return $this->renderPdf('pdf.class-summary', [
                'logoPath' => $this->fileToDataUri(
                    $setting?->logo_path
                        ? storage_path('app/public/' . $setting->logo_path)
                        : public_path('images/placeholder-logo.png')
                ),
                'schoolName' => $setting?->organization_name ?? 'School Name',
                'schoolAddress' => $setting?->short_address ?? $setting?->street_address ?? 'School Address',
                'examTitle' => strtoupper($setup?->exam?->name ?? 'EXAM') . ' - ' . ($setup?->academicYear?->name ?? now()->format('Y')),
                'results' => $results,
                'issueDate' => now()->format('Y-m-d'),
            ]);
        }

        $studentsData = $results->map(fn ($result) => $this->prepareStudentData($result))->values()->toArray();

        $html = view("pdf.{$template}", ['studentsData' => $studentsData])->render();

        return $this->outputPdf($html);
    }

    private function renderPdf(string $view, array $data): string
    {
        $html = view($view, $data)->render();

        return $this->outputPdf($html);
    }

    private function outputPdf(string $html): string
    {
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf->output();
    }

    public function prepareStudentData(StudentResult $result): array
    {
        $setting = $this->getSetting();
        $student = $result->student;

        $regularResults = $result->subjectResults->where('is_gpa_counted', true);
        $gradeOnlyResults = $result->subjectResults->where('is_gpa_counted', false);

        $subjectRows = $regularResults->map(fn ($sr) => [
            'name' => strtoupper($sr->subject->name ?? ''),
            'th_credit' => $sr->theory_credit_hour !== null ? number_format($sr->theory_credit_hour, 2) : '-',
            'th_point' => $sr->theory_point !== null ? number_format($sr->theory_point, 1) : '-',
            'th_grade' => $sr->theory_grade ?? '-',
            'pr_credit' => $sr->practical_credit_hour !== null ? number_format($sr->practical_credit_hour, 2) : '-',
            'pr_point' => $sr->practical_point !== null ? number_format($sr->practical_point, 1) : '-',
            'pr_grade' => $sr->practical_grade ?? '-',
            'final_grade' => $sr->final_grade ?? '-',
            'remarks' => $sr->remarks ?? '-',
        ])->values()->toArray();

        $gradeOnlySubjects = $gradeOnlyResults->map(fn ($sr) => [
            'name' => strtoupper($sr->subject->name ?? ''),
            'grade' => $sr->final_grade ?? '-',
        ])->values()->toArray();

        // Resolve image paths to base64 data URIs — no HTTP requests needed
        $logoPath = $this->fileToDataUri(
            $setting?->logo_path
                ? storage_path('app/public/' . $setting->logo_path)
                : public_path('images/placeholder-logo.png')
        );
        $principalSignPath = $this->fileToDataUri(
            $setting?->principal_sign_path ? storage_path('app/public/' . $setting->principal_sign_path) : null
        );
        $sealPath = $this->fileToDataUri(
            $setting?->seal_path ? storage_path('app/public/' . $setting->seal_path) : null
        );

        $photoPath = null;
        if ($student?->student_image) {
            $photoPath = $this->fileToDataUri(storage_path('app/public/' . $student->student_image));
        }

        $overallRemarks = $this->overallRemarkFor($result->result, $result->gpa ? (float) $result->gpa : null);

        return [
            'schoolName' => $setting?->organization_name ?? 'School Name',
            'schoolAddress' => $setting?->short_address ?? $setting?->street_address ?? 'School Address',
            'schoolSlogan' => $setting?->school_slogan,
            'examTitle' => strtoupper($result->examClassSetup?->exam?->name ?? 'EXAM') . ' - ' . ($result->examClassSetup?->academicYear?->name ?? now()->format('Y')),
            'logoPath' => $logoPath,
            'principalSignPath' => $principalSignPath,
            'sealPath' => $sealPath,
            'photoPath' => $photoPath,
            'studentName' => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')),
            'studentId' => $student?->regd_no ?? '-',
            'className' => strtoupper($result->examClassSetup?->schoolClass?->name ?? '-'),
            'sectionName' => $student?->section?->name ?? '-',
            'rollNo' => $student?->roll_no ?? $student?->id ?? '-',
            'subjectRows' => $subjectRows,
            'gradeOnlySubjects' => $gradeOnlySubjects,
            'attendancePresent' => $result->attendance_present ?? '-',
            'attendanceTotal' => $result->attendance_total ?? '-',
            'gpa' => $result->result === 'FAIL' ? '-' : ($result->gpa ?? '-'),
            'remarks' => $overallRemarks,
            'issueDate' => now()->format('Y-m-d'),
        ];
    }

    protected function overallRemarkFor(?string $resultStatus, ?float $gpa): string
    {
        if ($resultStatus === 'FAIL') {
            return 'Needs improvement. Please work harder.';
        }

        if ($gpa === null) {
            return '-';
        }

        return match (true) {
            $gpa >= 3.6 => 'Outstanding performance! Keep it up.',
            $gpa >= 3.2 => 'Excellent work! Keep it up.',
            $gpa >= 2.8 => 'Very good! Keep working hard.',
            $gpa >= 2.4 => 'Good! Keep working hard.',
            default => 'Satisfactory. More effort needed.',
        };
    }
}