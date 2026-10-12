<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Program; // ✅ Added to satisfy table requirements
use Illuminate\Database\Seeder;

class AcademicSetupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Academic Programs (Required by sections table)
        $generalProgram = Program::create([
            'name' => 'Regular Schooling Program',
            // If your table has an 'is_active' or code field, you can add it here safely
        ]);

        $plusTwoProgram = Program::create([
            'name' => 'Higher Secondary Plus Two Program',
        ]);


        // 2. Seed Academic Faculties (Streams)
        $general = Faculty::create([
            'name' => 'General Schooling',
            'is_active' => true
        ]);

        $science = Faculty::create([
            'name' => 'Science (10+2)',
            'is_active' => true
        ]);

        $management = Faculty::create([
            'name' => 'Management (10+2)',
            'is_active' => true
        ]);


        // 3. Seed Academic Education Levels
        $basicLevel = Level::create([
            'name' => 'Basic Education (Class 1-8)',
            'is_active' => true
        ]);

        $secondaryLevel = Level::create([
            'name' => 'Secondary Education (Class 9-10)',
            'is_active' => true
        ]);

        $higherSecondary = Level::create([
            'name' => 'Higher Secondary (Class 11-12)',
            'is_active' => true
        ]);


        // 4. Seed School Classes (Mapping them to Levels & Faculties)
        $class8 = SchoolClass::create([
            'name' => 'Class 8',
            'level_id' => $basicLevel->id,
            'faculty_id' => $general->id,
        ]);

        $class9 = SchoolClass::create([
            'name' => 'Class 9',
            'level_id' => $secondaryLevel->id,
            'faculty_id' => $general->id,
        ]);

        $class11Sci = SchoolClass::create([
            'name' => 'Class 11',
            'level_id' => $higherSecondary->id,
            'faculty_id' => $science->id,
        ]);


        // 5. Seed Sections (Now passing the required program_id)
        // Class 8 Sections
        Section::create([
            'name' => 'Section A', 
            'school_class_id' => $class8->id, 
            'program_id' => $generalProgram->id, // ✅ Fixed
            'is_active' => true
        ]);
        Section::create([
            'name' => 'Section B', 
            'school_class_id' => $class8->id, 
            'program_id' => $generalProgram->id, // ✅ Fixed
            'is_active' => true
        ]);

        // Class 9 Sections
        Section::create([
            'name' => 'Section A', 
            'school_class_id' => $class9->id, 
            'program_id' => $generalProgram->id, // ✅ Fixed
            'is_active' => true
        ]);
        Section::create([
            'name' => 'Section B', 
            'school_class_id' => $class9->id, 
            'program_id' => $generalProgram->id, // ✅ Fixed
            'is_active' => true
        ]);

        // Class 11 Science Sections
        Section::create([
            'name' => 'Sigma', 
            'school_class_id' => $class11Sci->id, 
            'program_id' => $plusTwoProgram->id, // ✅ Fixed
            'is_active' => true
        ]);
        Section::create([
            'name' => 'Delta', 
            'school_class_id' => $class11Sci->id, 
            'program_id' => $plusTwoProgram->id, // ✅ Fixed
            'is_active' => true
        ]);
    }
}