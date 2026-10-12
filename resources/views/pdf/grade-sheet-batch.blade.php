<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { size: A4 portrait; margin: 10mm 8mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
    .outer-border { border: 3px double #2e1a6b; padding: 8px; }
    .center { text-align: center; }
    .school-name { color: #2e1a6b; font-size: 18px; font-weight: bold; margin: 0; }
    .school-address { font-style: italic; margin: 1px 0; font-size: 10px; }
    .exam-title { color: #b8145a; font-weight: bold; text-decoration: underline; font-size: 12px; }
    .badge { background: #2e1a6b; color: #fff; padding: 3px 14px; font-weight: bold; display: inline-block; font-size: 11px; }
    .header-table { width: 100%; }
    .header-table td { vertical-align: top; }
    .logo { width: 55px; }
    .photo { width: 55px; border: 1px solid #333; }
    .info-table { width: 100%; border: 1px solid #2e1a6b; border-collapse: collapse; margin-top: 6px; font-size: 10px; }
    .info-table td { padding: 2px 8px; color: #2e1a6b; font-weight: bold; }
    .subjects-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; }
    .subjects-table th, .subjects-table td { border: 1px solid #2e1a6b; padding: 2px 5px; text-align: center; line-height: 1.2; }
    .subjects-table th { background: #e5e0f5; }
    .subj-name { text-align: left; }
    .bottom-table { width: 100%; margin-top: 6px; }
    .bottom-table td { vertical-align: top; padding-right: 8px; }
    .grading-box, .stats-box { width: 100%; border-collapse: collapse; font-size: 9px; }
    .grading-box th, .grading-box td, .stats-box th, .stats-box td { border: 1px solid #2e1a6b; padding: 2px 6px; }
    .stats-box th { background: #e5e0f5; text-align: center; }
    .stats-box td { text-align: center; }
    .remarks-box { border: 1px solid #2e1a6b; padding: 4px; margin-top: 6px; font-size: 10px; }
    .remarks-text { color: #1a7a1a; font-style: italic; text-align: center; }
    .sign-table { width: 100%; margin-top: 30px; }
    .sign-table td { text-align: center; border-top: 1px solid #000; padding-top: 2px; font-weight: bold; font-size: 9px; }
    .legend-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .legend-table th, .legend-table td { border: 1px solid #2e1a6b; padding: 2px 5px; font-size: 8px; text-align: center; }
    .legend-table th { background: #e5e0f5; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>
@foreach ($studentsData as $index => $data)
    <div @if ($index > 0) class="page-break" @endif>
        @include('pdf._body', $data)
    </div>
@endforeach
</body>
</html>