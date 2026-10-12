<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'academic_year_id')) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->constrained('academic_fiscal_years')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'batch_year_id')) {
                $table->foreignId('batch_year_id')
                    ->nullable()
                    ->constrained('batch_years')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'class_id')) {
                $table->foreignId('class_id')
                    ->nullable()
                    ->constrained('school_classes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'section_id')) {
                $table->foreignId('section_id')
                    ->nullable()
                    ->constrained('sections')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'gender')) {
                $table->string('gender', 20)->nullable();
            }

            if (! Schema::hasColumn('students', 'mobile_no')) {
                $table->string('mobile_no', 15)->nullable();
            }

            if (! Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name', 100)->nullable();
            }

            if (! Schema::hasColumn('students', 'guardian_relation')) {
                $table->string('guardian_relation', 50)->nullable();
            }

            if (! Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone', 15)->nullable();
            }

            if (! Schema::hasColumn('students', 'perm_state_id')) {
                $table->foreignId('perm_state_id')
                    ->nullable()
                    ->constrained('states')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'perm_district_id')) {
                $table->foreignId('perm_district_id')
                    ->nullable()
                    ->constrained('districts')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'perm_municipality_id')) {
                $table->foreignId('perm_municipality_id')
                    ->nullable()
                    ->constrained('municipalities')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'perm_ward')) {
                $table->string('perm_ward', 10)->nullable();
            }

            if (! Schema::hasColumn('students', 'perm_tole')) {
                $table->string('perm_tole', 100)->nullable();
            }

            if (! Schema::hasColumn('students', 'student_image')) {
                $table->string('student_image')->nullable();
            }

            if (! Schema::hasColumn('students', 'document_path')) {
                $table->text('document_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'academic_year_id')) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
            if (Schema::hasColumn('students', 'batch_year_id')) {
                $table->dropForeign(['batch_year_id']);
                $table->dropColumn('batch_year_id');
            }
            if (Schema::hasColumn('students', 'class_id')) {
                $table->dropForeign(['class_id']);
                $table->dropColumn('class_id');
            }
            if (Schema::hasColumn('students', 'section_id')) {
                $table->dropForeign(['section_id']);
                $table->dropColumn('section_id');
            }
            if (Schema::hasColumn('students', 'perm_state_id')) {
                $table->dropForeign(['perm_state_id']);
                $table->dropColumn('perm_state_id');
            }
            if (Schema::hasColumn('students', 'perm_district_id')) {
                $table->dropForeign(['perm_district_id']);
                $table->dropColumn('perm_district_id');
            }
            if (Schema::hasColumn('students', 'perm_municipality_id')) {
                $table->dropForeign(['perm_municipality_id']);
                $table->dropColumn('perm_municipality_id');
            }
            if (Schema::hasColumn('students', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('students', 'mobile_no')) {
                $table->dropColumn('mobile_no');
            }
            if (Schema::hasColumn('students', 'guardian_name')) {
                $table->dropColumn('guardian_name');
            }
            if (Schema::hasColumn('students', 'guardian_relation')) {
                $table->dropColumn('guardian_relation');
            }
            if (Schema::hasColumn('students', 'guardian_phone')) {
                $table->dropColumn('guardian_phone');
            }
            if (Schema::hasColumn('students', 'perm_ward')) {
                $table->dropColumn('perm_ward');
            }
            if (Schema::hasColumn('students', 'perm_tole')) {
                $table->dropColumn('perm_tole');
            }
            if (Schema::hasColumn('students', 'student_image')) {
                $table->dropColumn('student_image');
            }
            if (Schema::hasColumn('students', 'document_path')) {
                $table->dropColumn('document_path');
            }
        });
    }
};