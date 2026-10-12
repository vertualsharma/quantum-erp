<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "2083"
            $table->string('english_name')->nullable(); // e.g., "2026/27"
            $table->boolean('is_current')->default(false); // Yes/No
            $table->string('status')->default('Active');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_fiscal_years');
    }
};