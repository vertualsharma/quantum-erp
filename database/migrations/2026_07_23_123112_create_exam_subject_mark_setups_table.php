<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subject_mark_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_class_setup_id')->constrained('exam_class_setups')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            // Theory Marks Breakdown
            $table->decimal('theory_full_marks', 5, 2)->default(75.00);
            $table->decimal('theory_pass_marks', 5, 2)->default(27.00);

            // Practical Marks Breakdown
            $table->decimal('practical_full_marks', 5, 2)->default(25.00);
            $table->decimal('practical_pass_marks', 5, 2)->default(10.00);

            // Total Full Marks & Pass Marks
            $table->decimal('total_full_marks', 5, 2)->default(100.00);
            $table->decimal('total_pass_marks', 5, 2)->default(37.00);

            $table->timestamps();

            $table->unique(['exam_class_setup_id', 'subject_id'], 'exam_setup_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_mark_setups');
    }
};