<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['code' => '1', 'name' => 'ENGLISH', 'short_name' => 'ENGLISH', 'position' => 1],
            ['code' => '2', 'name' => 'NEPALI', 'short_name' => 'NEPALI', 'position' => 2],
            ['code' => '3', 'name' => 'MATHEMATICS', 'short_name' => 'MATHEMATICS', 'position' => 3],
            ['code' => '4', 'name' => 'SCIENCE AND TECHNOLOGY', 'short_name' => 'SCIENCE AND TECH', 'position' => 4],
            ['code' => '5', 'name' => 'SAMAJIK ADHYAYAN', 'short_name' => 'SAMAJIK ADHYAYAN', 'position' => 5],
            ['code' => '6', 'name' => 'HEALTH & CREATIVE ARTS', 'short_name' => 'HEALTH & CREATIVE ARTS', 'position' => 6],
            ['code' => '7', 'name' => 'COMPUTER SCIENCE', 'short_name' => 'COMPUTER SCIENCE', 'position' => 7],
            ['code' => '8', 'name' => 'OPTIONAL MATHS', 'short_name' => 'OPTIONAL I', 'position' => 8],
        ];

        foreach ($subjects as $s) {
            Subject::firstOrCreate(['code' => $s['code']], $s);
        }
    }
}