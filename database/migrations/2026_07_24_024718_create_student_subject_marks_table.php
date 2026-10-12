<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_subject_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_mark_setup_id')->constrained('exam_subject_mark_setups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('theory_obtained', 6, 2)->nullable();
            $table->boolean('theory_absent')->default(false);
            $table->decimal('practical_obtained', 6, 2)->nullable();
            $table->boolean('practical_absent')->default(false);
            $table->timestamps();

            $table->unique(['exam_subject_mark_setup_id', 'student_id'], 'student_subject_mark_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_marks');
    }
};