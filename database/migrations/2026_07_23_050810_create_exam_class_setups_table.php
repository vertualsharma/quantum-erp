<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_class_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_fiscal_years')->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->integer('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('mark_entry_locked')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'school_class_id', 'academic_year_id'], 'exam_class_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_class_setups');
    }
};