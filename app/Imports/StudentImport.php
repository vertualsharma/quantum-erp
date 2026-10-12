<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Municipality;
use App\Models\State;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function __construct(
        protected int $academicYearId,
        protected int $batchYearId,
        protected int $facultyId,
        protected int $classId,
        protected ?int $sectionId = null,
    ) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $data = [
                'first_name'          => trim((string) ($row['first_name'] ?? '')),
                'last_name'           => trim((string) ($row['last_name'] ?? '')),
                'gender'              => ucfirst(strtolower(trim((string) ($row['gender'] ?? '')))),
                'dob_ad'              => $this->parseAdDate($row['dob_ad'] ?? null),
                'guardian_name'       => trim((string) ($row['guardian_name'] ?? '')),
                'guardian_relation'   => ucfirst(strtolower(trim((string) ($row['guardian_relation'] ?? '')))),
                'guardian_phone'      => trim((string) ($row['guardian_phone'] ?? '')),
                'perm_ward'           => isset($row['perm_ward']) ? (string) $row['perm_ward'] : null,
                'perm_tole'           => $row['perm_tole'] ?? null,
                'email'               => $row['email'] ?? null,
                'campus_email'        => $row['campus_email'] ?? null,
                'dob_bs'              => $this->rawDateString($row['dob_bs'] ?? null),
                'age'                 => isset($row['age']) && $row['age'] !== '' ? (int) $row['age'] : null,
                'passed_level'        => $row['passed_level'] ?? null,
                'passed_institution'  => $row['passed_institution'] ?? null,
                'passed_gpa'          => isset($row['passed_gpa']) && $row['passed_gpa'] !== '' ? (float) $row['passed_gpa'] : null,
                'religion'            => $row['religion'] ?? null,
                'ethnicity'           => $row['ethnicity'] ?? null,
                'mother_tongue'       => $row['mother_tongue'] ?? null,
                'admission_date_bs'   => $this->rawDateString($row['admission_date_bs'] ?? null),
            ];

            $validator = Validator::make($data, [
                'first_name'        => ['required', 'string', 'max:50'],
                'last_name'         => ['required', 'string', 'max:50'],
                'gender'            => ['required', Rule::in(['Male', 'Female', 'Other'])],
                'dob_ad'            => ['required', 'date', 'before_or_equal:today'],
                'guardian_name'     => ['required', 'string', 'max:100'],
                'guardian_relation' => ['required', Rule::in(['Father', 'Mother', 'Brother', 'Sister', 'Other'])],
                'guardian_phone'    => ['required', 'string', 'max:15'],
                'perm_ward'         => ['nullable', 'string', 'max:5'],
                'perm_tole'         => ['nullable', 'string', 'max:100'],
                'email'             => ['nullable', 'string', 'max:150'],
                'campus_email'      => ['nullable', 'string', 'max:150'],
                'age'               => ['nullable', 'integer', 'min:0', 'max:100'],
                'passed_gpa'        => ['nullable', 'numeric', 'min:0', 'max:4'],
            ]);

            if ($validator->fails()) {
                $this->skipped++;
                $this->errors[] = "Row {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            $data['academic_year_id'] = $this->academicYearId;
            $data['batch_year_id']    = $this->batchYearId;
            $data['faculty_id']       = $this->facultyId;
            $data['class_id']         = $this->classId;
            $data['section_id']       = $this->sectionId;

            $data['perm_state_id']        = $this->resolveLocation(State::class, $row['perm_state_id'] ?? null);
            $data['perm_district_id']     = $this->resolveLocation(District::class, $row['perm_district_id'] ?? null);
            $data['perm_municipality_id'] = $this->resolveLocation(Municipality::class, $row['perm_municipality_id'] ?? null);

            try {
                Student::create($data);
                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    /** AD dates only — safe to run through Carbon/Excel-serial parsing */
    protected function parseAdDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** BS dates — no calendar conversion, just normalize to a Y-m-d-looking string */
    protected function rawDateString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            // Excel serial — format the raw date parts without treating it as a real Gregorian date
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        return trim((string) $value);
    }

    protected function resolveLocation(string $modelClass, $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $modelClass::where('name', trim((string) $value))->value('id');
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}