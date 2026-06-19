<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('ugc_id')->nullable();
            $table->string('short_name');
            $table->string('name');
            $table->string('university')->nullable();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('YEAR');
            $table->integer('no_of_years')->default(0);
            $table->string('start_date_bs')->nullable();
            $table->date('start_date_ad')->nullable();
            $table->string('approval_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};