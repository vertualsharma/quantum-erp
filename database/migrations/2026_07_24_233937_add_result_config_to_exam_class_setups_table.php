<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_class_setups', function (Blueprint $table) {
            $table->string('attendance_type', 30)->nullable()->after('total_attendance_days');
            $table->string('result_decision_type', 30)->nullable()->after('attendance_type');
            $table->string('result_template', 100)->nullable()->after('result_decision_type');
            $table->string('remarks_type', 30)->nullable()->after('result_template');
        });
    }

    public function down(): void
    {
        Schema::table('exam_class_setups', function (Blueprint $table) {
            $table->dropColumn(['attendance_type', 'result_decision_type', 'result_template', 'remarks_type']);
        });
    }
};