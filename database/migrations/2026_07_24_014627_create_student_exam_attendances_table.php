<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_exam_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_class_setup_id')->constrained('exam_class_setups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('days_present')->default(0);
            $table->timestamps();

            $table->unique(['exam_class_setup_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exam_attendances');
    }
};