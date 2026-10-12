<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .center { text-align: center; }
        .school-name { color: #2e1a6b; font-size: 18px; font-weight: bold; margin: 0; }
        .school-address { font-style: italic; margin: 1px 0; font-size: 10px; }
        .exam-title { color: #b8145a; font-weight: bold; text-decoration: underline; font-size: 12px; }
        .badge { background: #2e1a6b; color: #fff; padding: 3px 14px; font-weight: bold; display: inline-block; font-size: 11px; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .logo { width: 55px; }
        .summary-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .summary-table th, .summary-table td { border: 1px solid #2e1a6b; padding: 4px 6px; text-align: center; }
        .summary-table th { background: #e5e0f5; color: #2e1a6b; font-weight: bold; }
        .fail-row { background-color: #fff0f0; }
        .sign-table { width: 100%; margin-top: 30px; }
        .sign-table td { text-align: center; border-top: 1px solid #000; padding-top: 5px; font-weight: bold; font-size: 9px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60px;"><img class="logo" src="{{ $logoPath }}"></td>
            <td class="center">
                <div style="font-style: italic; font-size: 10px;">"Education For Excellence"</div>
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-address">{{ $schoolAddress }}</div>
                <div class="exam-title">{{ $examTitle }}</div>
                <div style="margin-top: 4px;"><span class="badge">CLASS RESULT SUMMARY</span></div>
            </td>
            <td style="width: 60px;"></td>
        </tr>
    </table>

    <table class="summary-table">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>Section</th>
                <th>Attendance</th>
                <th>GPA</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $i => $res)
                <tr class="{{ $res->result === 'FAIL' ? 'fail-row' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $res->student->roll_no ?? $res->student->id }}</td>
                    <td style="text-align: left;">{{ trim($res->student->first_name . ' ' . $res->student->last_name) }}</td>
                    <td>{{ $res->student->section?->name ?? '-' }}</td>
                    <td>{{ $res->attendance_present }} / {{ $res->attendance_total }}</td>
                    <td>{{ $res->result === 'FAIL' ? '-' : ($res->gpa ?? '-') }}</td>
                    <td style="font-weight: bold;">{{ $res->result }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td style="border-top: none; text-align: left; font-weight: normal;">Issued On : {{ $issueDate }}</td>
            <td>Class Teacher</td>
            <td>School Seal</td>
            <td>Principal</td>
        </tr>
    </table>
</body>
</html>