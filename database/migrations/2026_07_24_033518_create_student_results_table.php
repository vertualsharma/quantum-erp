<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_class_setup_id')->constrained('exam_class_setups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->decimal('total_credit_hour', 6, 2)->nullable();
            $table->string('result', 10)->nullable(); // PASS / FAIL
            $table->integer('attendance_present')->nullable();
            $table->integer('attendance_total')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_class_setup_id', 'student_id'], 'student_result_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};