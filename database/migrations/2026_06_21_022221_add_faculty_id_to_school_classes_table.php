<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            // Adds the faculty structural link constraint
            $table->foreignId('faculty_id')
                  ->nullable() 
                  ->after('id') // Cleans up positioning in DB viewers
                  ->constrained('faculties')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn('faculty_id');
        });
    }
};