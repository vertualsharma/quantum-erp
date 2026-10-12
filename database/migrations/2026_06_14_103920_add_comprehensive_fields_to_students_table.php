<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert to DYNAMIC format FIRST (before adding columns)
        DB::statement('ALTER TABLE students ROW_FORMAT=DYNAMIC');
        
        // Disable strict mode
        DB::statement('SET SESSION innodb_strict_mode=OFF');

        // Now add columns
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'temp_country')) {
                $table->string('temp_country', 50)->default('Nepal')->nullable();
            }
            // Add other fields here
        });

        // Re-enable strict mode
        DB::statement('SET SESSION innodb_strict_mode=ON');
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'temp_country')) {
                $table->dropColumn('temp_country');
            }
        });
    }
};