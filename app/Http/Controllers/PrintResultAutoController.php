<?php

namespace App\Http\Controllers;

use App\Services\PrintResultService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrintResultAutoController extends Controller
{
    public function single(Request $request, int $studentResultId): Response
    {
        $template = $request->query('template', 'grade-sheet');

        $service = new PrintResultService();
        $pdfContent = $service->generate($studentResultId, $template);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="grade-sheet.pdf"',
        ]);
    }

    public function wholeClass(Request $request, int $examClassSetupId): Response
    {
        $sectionId = $request->query('section_id');
        $template = $request->query('template', 'grade-sheet-batch');

        $service = new PrintResultService();
        $pdfContent = $service->generateForClass(
            $examClassSetupId,
            $sectionId ? (int) $sectionId : null,
            $template
        );

        $fileName = match ($template) {
            'class-summary' => 'class-summary.pdf',
            default => 'whole-class-grade-sheets.pdf',
        };

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}