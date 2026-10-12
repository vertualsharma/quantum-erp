<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_derivations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_derivation_id')->constrained('exam_derivations')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['exam_derivation_id', 'subject_id'], 'subject_derivation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_derivations');
    }
};