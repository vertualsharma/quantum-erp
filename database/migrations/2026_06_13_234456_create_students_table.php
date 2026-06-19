<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // Academic Info
            $table->string('batch_year');         // e.g., "2083 - 2026"
            $table->string('fiscal_year');        // e.g., "2080"
            $table->string('joined_batch_year');  // e.g., "2083 - 2026"
            $table->string('regd_no')->nullable();
            $table->string('iemis_code')->nullable();

            // Core Relationships (Built in Phase 1!)
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();

            // Personal Info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable(); // Standard requirement
            $table->string('email')->nullable();
            $table->string('campus_email')->nullable();
            $table->string('dob_bs')->nullable();     // Nepali Date (String)
            $table->date('dob_ad')->nullable();       // English Date
            $table->integer('age')->nullable();

            // Previous Academic Records
            $table->string('passed_level')->nullable();
            $table->string('passed_institution')->nullable();
            $table->string('passed_gpa')->nullable();

            // Demographics
            $table->string('religion')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('admission_date_bs')->nullable(); // e.g., "2083-02-31"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};