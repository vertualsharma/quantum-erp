<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\ExamClassSetup;
use App\Models\Student;
use App\Models\StudentResult;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LedgerExportService
{
    /**
     * Build a Mark or Grade ledger spreadsheet for a class/section/exam.
     * $type is 'mark' or 'grade'.
     */
    public function build(int $examClassSetupId, ?int $sectionId, string $type): Spreadsheet
    {
        $examClassSetup = ExamClassSetup::with(['exam', 'schoolClass', 'program', 'academicYear'])->findOrFail($examClassSetupId);

        $subjects = ClassSubject::with('subject')
            ->where('school_class_id', $examClassSetup->school_class_id)
            ->where('is_active', true)
            ->get()
            ->unique('subject_id')
            ->sortBy('subject.position');

        $students = Student::where('class_id', $examClassSetup->school_class_id)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->orderBy('id')
            ->get();

        $results = StudentResult::with('subjectResults')
            ->where('exam_class_setup_id', $examClassSetupId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $totalCols = 2 + ($subjects->count() * 3) + 3;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        $sheet->setCellValue('A1', 'Aims Educational Academy');
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $label = $type === 'grade' ? 'Grade Ledger' : 'Mark Ledger';
        $yearLabel = $examClassSetup->academicYear->name ?? now()->format('Y');
        $sheet->setCellValue('A2', "{$label} ({$examClassSetup->exam->name}) -{$yearLabel}");
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sectionName = $sectionId ? \App\Models\Section::find($sectionId)?->name : '';
        $sheet->setCellValue('A3', 'Program');
        $sheet->setCellValue('B3', $examClassSetup->program?->name ?? '-');
        $sheet->setCellValue('D3', 'Class');
        $sheet->setCellValue('E3', $examClassSetup->schoolClass->name);
        $sheet->setCellValue('G3', 'Section');
        $sheet->setCellValue('H3', $sectionName ?: '-');
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);

        $col = 3;
        $sheet->setCellValue('B5', 'Subjects');
        $sheet->getStyle('B5')->getFont()->setBold(true);
        $sheet->setCellValue('B6', 'FM');
        $sheet->setCellValue('B7', 'PM');
        $sheet->getStyle('B6:B7')->getFont()->setBold(true);

        foreach ($subjects as $classSubject) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2);
            $sheet->setCellValue("{$startCol}5", $classSubject->subject->name);
            $sheet->mergeCells("{$startCol}5:{$endCol}5");
            $sheet->getStyle("{$startCol}5")->getFont()->setBold(true);
            $sheet->getStyle("{$startCol}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue("{$startCol}6", $classSubject->grading_type === 'Regular' ? (float) $this->fullMarkFor($examClassSetupId, $classSubject->subject_id, 'theory') : 0);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '6', $classSubject->grading_type === 'Regular' ? (float) $this->fullMarkFor($examClassSetupId, $classSubject->subject_id, 'practical') : 0);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2) . '6', $classSubject->grading_type === 'Regular' ? (float) $this->fullMarkFor($examClassSetupId, $classSubject->subject_id, 'theory') + (float) $this->fullMarkFor($examClassSetupId, $classSubject->subject_id, 'practical') : 0);

            $sheet->setCellValue("{$startCol}7", $classSubject->grading_type === 'Regular' ? (float) $this->passMarkFor($examClassSetupId, $classSubject->subject_id, 'theory') : 0);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '7', $classSubject->grading_type === 'Regular' ? (float) $this->passMarkFor($examClassSetupId, $classSubject->subject_id, 'practical') : 0);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2) . '7', $classSubject->grading_type === 'Regular' ? (float) $this->passMarkFor($examClassSetupId, $classSubject->subject_id, 'theory') + (float) $this->passMarkFor($examClassSetupId, $classSubject->subject_id, 'practical') : 0);

            $sheet->setCellValue("{$startCol}8", 'TH');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '8', 'PR');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2) . '8', 'Total');

            $col += 3;
        }

        $totalStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue("{$totalStartCol}5", 'Total');
        $sheet->mergeCells("{$totalStartCol}5:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2) . '5');
        $sheet->getStyle("{$totalStartCol}5")->getFont()->setBold(true);

        $sheet->setCellValue('A8', 'Roll No');
        $sheet->setCellValue('B8', 'Name');
        $sheet->getStyle('A8:' . $lastColLetter . '8')->getFont()->setBold(true);

        $rowNum = 9;
        foreach ($students as $student) {
            $sheet->setCellValue("A{$rowNum}", $student->id);
            $sheet->setCellValue("B{$rowNum}", trim($student->first_name . ' ' . $student->last_name));

            $studentResult = $results->get($student->id);
            $col = 3;

            foreach ($subjects as $classSubject) {
                $subjectResult = $studentResult?->subjectResults->firstWhere('subject_id', $classSubject->subject_id);

                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $prCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $totalCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2);

                if ($classSubject->grading_type === 'Regular') {
                    if ($type === 'grade') {
                        $sheet->setCellValue("{$startCol}{$rowNum}", $subjectResult?->theory_grade ?? '-');
                        $sheet->setCellValue("{$prCol}{$rowNum}", $subjectResult?->practical_grade ?? '-');
                        $sheet->setCellValue("{$totalCol}{$rowNum}", $subjectResult?->final_grade ?? '-');
                    } else {
                        $theoryRaw = $this->rawMark($studentResult?->id, $classSubject->subject_id, 'theory');
                        $practicalRaw = $this->rawMark($studentResult?->id, $classSubject->subject_id, 'practical');
                        $sheet->setCellValue("{$startCol}{$rowNum}", $theoryRaw);
                        $sheet->setCellValue("{$prCol}{$rowNum}", $practicalRaw);
                        $sheet->setCellValue("{$totalCol}{$rowNum}", (is_numeric($theoryRaw) && is_numeric($practicalRaw)) ? $theoryRaw + $practicalRaw : '-');
                    }
                } else {
                    $sheet->setCellValue("{$startCol}{$rowNum}", $subjectResult?->final_grade ?? '-');
                }

                $col += 3;
            }

            $totalCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

            if ($type === 'grade') {
                $overallGrade = $this->overallGradeFor($studentResult?->gpa ? (float) $studentResult->gpa : null);
                $resultLabel = $studentResult?->result ?? '-';
                $sheet->setCellValue("{$totalCol}{$rowNum}", "{$overallGrade} ({$resultLabel})");
            } else {
                $totalMarks = 0;
                $hasAnyMark = false;

                foreach ($subjects as $ts) {
                    if ($ts->grading_type !== 'Regular') {
                        continue;
                    }

                    $th = $this->rawMark($studentResult?->id, $ts->subject_id, 'theory');
                    $pr = $this->rawMark($studentResult?->id, $ts->subject_id, 'practical');

                    if (is_numeric($th)) {
                        $totalMarks += $th;
                        $hasAnyMark = true;
                    }
                    if (is_numeric($pr)) {
                        $totalMarks += $pr;
                        $hasAnyMark = true;
                    }
                }

                $sheet->setCellValue("{$totalCol}{$rowNum}", $hasAnyMark ? $totalMarks : '-');
            }

            $rowNum++;
        }

        $sheet->getStyle("A8:{$lastColLetter}" . ($rowNum - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    protected function fullMarkFor(int $examClassSetupId, int $subjectId, string $component): float
    {
        $markSetup = \App\Models\ExamSubjectMarkSetup::where('exam_class_setup_id', $examClassSetupId)
            ->where('subject_id', $subjectId)
            ->first();

        return $component === 'theory' ? (float) ($markSetup?->theory_full_marks ?? 0) : (float) ($markSetup?->practical_full_marks ?? 0);
    }

    protected function passMarkFor(int $examClassSetupId, int $subjectId, string $component): float
    {
        $markSetup = \App\Models\ExamSubjectMarkSetup::where('exam_class_setup_id', $examClassSetupId)
            ->where('subject_id', $subjectId)
            ->first();

        return $component === 'theory' ? (float) ($markSetup?->theory_pass_marks ?? 0) : (float) ($markSetup?->practical_pass_marks ?? 0);
    }

    protected function rawMark(?int $studentResultId, int $subjectId, string $component): string|float
    {
        if (! $studentResultId) {
            return '-';
        }

        $studentResult = StudentResult::find($studentResultId);
        $mark = \App\Models\StudentSubjectMark::whereHas('examSubjectMarkSetup', fn ($q) => $q
                ->where('exam_class_setup_id', $studentResult->exam_class_setup_id)
                ->where('subject_id', $subjectId))
            ->where('student_id', $studentResult->student_id)
            ->first();

        if (! $mark) {
            return '-';
        }

        return $component === 'theory'
            ? ($mark->theory_absent ? 'AB' : (float) $mark->theory_obtained)
            : ($mark->practical_absent ? 'AB' : (float) $mark->practical_obtained);
    }

    protected function overallGradeFor(?float $gpa): string
    {
        if ($gpa === null) {
            return '-';
        }

        return match (true) {
            $gpa >= 3.6 => 'A+',
            $gpa >= 3.2 => 'A',
            $gpa >= 2.8 => 'B+',
            $gpa >= 2.4 => 'B',
            $gpa >= 2.0 => 'C+',
            $gpa >= 1.6 => 'C',
            $gpa >= 1.2 => 'D',
            default => 'NG',
        };
    }
}