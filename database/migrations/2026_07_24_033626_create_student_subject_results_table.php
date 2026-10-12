<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_subject_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_result_id')->constrained('student_results')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            $table->decimal('theory_percent', 5, 2)->nullable();
            $table->string('theory_grade', 5)->nullable();
            $table->decimal('theory_point', 3, 1)->nullable();
            $table->decimal('theory_credit_hour', 5, 2)->nullable();

            $table->decimal('practical_percent', 5, 2)->nullable();
            $table->string('practical_grade', 5)->nullable();
            $table->decimal('practical_point', 3, 1)->nullable();
            $table->decimal('practical_credit_hour', 5, 2)->nullable();

            $table->string('final_grade', 5)->nullable();
            $table->boolean('is_gpa_counted')->default(true);
            $table->string('remarks', 100)->nullable();

            $table->timestamps();

            $table->unique(['student_result_id', 'subject_id'], 'student_subject_result_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_results');
    }
};