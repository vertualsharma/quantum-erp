<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_class_setups', function (Blueprint $table) {
            $table->integer('total_attendance_days')->nullable()->after('mark_entry_locked');
        });
    }

    public function down(): void
    {
        Schema::table('exam_class_setups', function (Blueprint $table) {
            $table->dropColumn('total_attendance_days');
        });
    }
};