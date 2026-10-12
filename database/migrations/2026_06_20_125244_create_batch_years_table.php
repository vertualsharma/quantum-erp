<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_years', function (Blueprint $table) {
            $table->id();
            $table->string('nepali_year'); // e.g., "2083"
            $table->string('english_year')->nullable(); // e.g., "2026"
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_years');
    }
};