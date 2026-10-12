<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->default('2083');
            
            // Relational drop-down references
            $table->foreignId('faculty_id')->nullable();
            $table->foreignId('level_id')->nullable();
            $table->foreignId('program_id')->nullable();
            $table->foreignId('school_class_id')->nullable();
            $table->foreignId('subject_id')->nullable();
            $table->foreignId('subject_group_id')->nullable();
            $table->foreignId('elective_group_id')->nullable();

            // Academic configuration fields
            $table->string('type')->default('Mark'); // Mark / Grade
            $table->string('grading_type')->default('Regular');
            $table->decimal('th_credit_hour', 5, 2)->default(0.00);
            $table->decimal('pr_credit_hour', 5, 2)->default(0.00);
            $table->decimal('total_credit_hour', 5, 2)->default(0.00);
            $table->boolean('has_practical')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
    }
};