<div class="outer-border">
    <table class="header-table">
        <tr>
            <td style="width: 60px;"><img class="logo" src="{{ $logoPath }}"></td>
            <td class="center">
                <div style="font-style: italic; font-size: 10px;">"Education For Excellence"</div>
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-address">{{ $schoolAddress }}</div>
                <div class="exam-title">{{ $examTitle }}</div>
                <div style="margin-top: 4px;"><span class="badge">GRADE-SHEET</span></div>
            </td>
            <td style="width: 60px;">
                @if ($photoPath)
                    <img class="photo" src="{{ $photoPath }}">
                @endif
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 15%;">Name</td>
            <td style="width: 35%;">{{ $studentName }}</td>
            <td style="width: 15%;">Grade</td>
            <td style="width: 35%;">{{ $className }}</td>
        </tr>
        <tr>
            <td>STUDENT ID</td>
            <td>{{ $studentId }}</td>
            <td>Section</td>
            <td>{{ $sectionName }} &nbsp;&nbsp;&nbsp; Roll No &nbsp; {{ $rollNo }}</td>
        </tr>
    </table>

    <table class="subjects-table">
        <thead>
            <tr>
                <th style="width:5%;">S.N.</th>
                <th class="subj-name" style="width:28%;">Subjects</th>
                <th style="width:10%;">Credit<br>Hour</th>
                <th style="width:12%;">Grade Point</th>
                <th style="width:10%;">Grade</th>
                <th style="width:12%;">Final Grade</th>
                <th style="width:23%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subjectRows as $i => $row)
                <tr>
                    <td rowspan="2">{{ $i + 1 }}</td>
                    <td class="subj-name">{{ $row['name'] }}(TH)</td>
                    <td>{{ $row['th_credit'] }}</td>
                    <td>{{ $row['th_point'] }}</td>
                    <td>{{ $row['th_grade'] }}</td>
                    <td rowspan="2">{{ $row['final_grade'] }}</td>
                    <td rowspan="2">{{ $row['remarks'] }}</td>
                </tr>
                <tr>
                    <td class="subj-name">{{ $row['name'] }}(IN)</td>
                    <td>{{ $row['pr_credit'] }}</td>
                    <td>{{ $row['pr_point'] }}</td>
                    <td>{{ $row['pr_grade'] }}</td>
                </tr>
            @endforeach
            @php $padTo = max(9, count($subjectRows)); @endphp
            @for ($i = count($subjectRows); $i < $padTo; $i++)
                <tr><td style="height: 16px;">&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td style="width: 38%;">
                <table class="grading-box">
                    <tr><th colspan="2">GRADING</th></tr>
                    @foreach ($gradeOnlySubjects as $i => $g)
                        <tr>
                            <td style="width: 10%;">{{ $i + 1 }}</td>
                            <td class="subj-name">{{ $g['name'] }} &nbsp;&nbsp;&nbsp; {{ $g['grade'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td style="width: 62%;">
                <table class="stats-box">
                    <tr><th>Attendance</th><th>GPA</th></tr>
                    <tr><td>{{ $attendancePresent }} / {{ $attendanceTotal }}</td><td>{{ $gpa }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="remarks-box">
        Remarks : <span class="remarks-text">"{{ $remarks }}"</span>
    </div>

    <table class="sign-table">
        <tr>
            <td style="border-top: none; text-align: left; font-weight: normal;">Issued On : {{ $issueDate }}</td>
            <td>Class Teacher</td>
            <td>School Seal</td>
            <td>Principal</td>
        </tr>
    </table>

    <table class="legend-table">
        <tr>
            <th colspan="4">DETAILS OF GRADE SHEET</th>
            <th colspan="4"></th>
        </tr>
        <tr>
            <th>Interval in Percent</th><th>Grade</th><th>G Point</th><th>Description</th>
            <th>Interval in Percent</th><th>Grade</th><th>G Point</th><th>Description</th>
        </tr>
        <tr>
            <td>90 to 100</td><td>A+</td><td>3.6-4.0</td><td>Outstanding</td>
            <td>50 to below 60</td><td>C+</td><td>2-2.4</td><td>Satisfactory</td>
        </tr>
        <tr>
            <td>80 to below 90</td><td>A</td><td>3.2-3.6</td><td>Excellent</td>
            <td>40 to below 50</td><td>C</td><td>1.6-2.0</td><td>Acceptable</td>
        </tr>
        <tr>
            <td>70 to below 80</td><td>B+</td><td>2.8-3.2</td><td>Very Good</td>
            <td>35 to below 40</td><td>D</td><td>1.2-1.6</td><td>Basic</td>
        </tr>
        <tr>
            <td>60 to below 70</td><td>B</td><td>2.4-2.8</td><td>Good</td>
            <td>Below 35</td><td>NG</td><td>-</td><td>Not Graded</td>
        </tr>
    </table>
</div>