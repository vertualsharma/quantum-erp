<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'first_name' => '',
                'last_name' => '',
                'gender' => '',
                'dob_ad' => '',
                'guardian_name' => '',
                'guardian_relation' => '',
                'guardian_phone' => '',
                'perm_state_id' => '',
                'perm_district_id' => '',
                'perm_municipality_id' => '',
                'perm_ward' => '',
                'perm_tole' => '',
                'email' => '',
                'campus_email' => '',
                'dob_bs' => '',
                'age' => '',
                'passed_level' => '',
                'passed_institution' => '',
                'passed_gpa' => '',
                'religion' => '',
                'ethnicity' => '',
                'mother_tongue' => '',
                'admission_date_bs' => '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'first_name',
            'last_name',
            'gender',
            'dob_ad',
            'guardian_name',
            'guardian_relation',
            'guardian_phone',
            'perm_state_id',
            'perm_district_id',
            'perm_municipality_id',
            'perm_ward',
            'perm_tole',
            'email',
            'campus_email',
            'dob_bs',
            'age',
            'passed_level',
            'passed_institution',
            'passed_gpa',
            'religion',
            'ethnicity',
            'mother_tongue',
            'admission_date_bs',
        ];
    }
}
