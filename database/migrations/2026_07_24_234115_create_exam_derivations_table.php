<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_derivations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_exam_class_setup_id')->constrained('exam_class_setups')->cascadeOnDelete();
            $table->foreignId('source_exam_class_setup_id')->constrained('exam_class_setups')->cascadeOnDelete();
            $table->decimal('percentage', 5, 2)->default(100);
            $table->boolean('is_main_exam')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['target_exam_class_setup_id', 'source_exam_class_setup_id'], 'exam_derivation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_derivations');
    }
};