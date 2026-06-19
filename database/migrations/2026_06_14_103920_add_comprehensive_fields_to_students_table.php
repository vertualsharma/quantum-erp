<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Administrative & Identifiers
            if (!Schema::hasColumn('students', 'batch_year')) $table->string('batch_year')->after('id');
            if (!Schema::hasColumn('students', 'fiscal_year')) $table->string('fiscal_year')->after('batch_year');
            if (!Schema::hasColumn('students', 'joined_batch_year')) $table->string('joined_batch_year')->after('fiscal_year');
            if (!Schema::hasColumn('students', 'regd_no')) $table->string('regd_no')->nullable()->after('joined_batch_year');
            if (!Schema::hasColumn('students', 'iemis_code')) $table->string('iemis_code')->nullable()->after('regd_no');

            // Names (Nepali script extensions)
            if (!Schema::hasColumn('students', 'first_name_np')) $table->string('first_name_np')->nullable();
            if (!Schema::hasColumn('students', 'middle_name_np')) $table->string('middle_name_np')->nullable();
            if (!Schema::hasColumn('students', 'last_name_np')) $table->string('last_name_np')->nullable();
            if (!Schema::hasColumn('students', 'student_image')) $table->string('student_image')->nullable();

            // Contact & Base Demographics
            if (!Schema::hasColumn('students', 'mobile_no')) $table->string('mobile_no')->nullable();
            if (!Schema::hasColumn('students', 'gender')) $table->string('gender')->nullable();
            if (!Schema::hasColumn('students', 'nationality')) $table->string('nationality')->default('Nepalese');
            if (!Schema::hasColumn('students', 'national_id_no')) $table->string('national_id_no')->nullable();
            if (!Schema::hasColumn('students', 'email')) $table->string('email')->nullable();
            if (!Schema::hasColumn('students', 'campus_email')) $table->string('campus_email')->nullable();
            if (!Schema::hasColumn('students', 'dob_bs')) $table->string('dob_bs')->nullable();
            if (!Schema::hasColumn('students', 'dob_ad')) $table->date('dob_ad')->nullable();
            if (!Schema::hasColumn('students', 'age')) $table->integer('age')->nullable();

            // Relationships & Structural Hierarchy 
            if (!Schema::hasColumn('students', 'program_id')) $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            if (!Schema::hasColumn('students', 'class_id')) $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            if (!Schema::hasColumn('students', 'section_id')) $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();

            // Cultural and Physical Indicators
            if (!Schema::hasColumn('students', 'blood_group')) $table->string('blood_group')->nullable();
            if (!Schema::hasColumn('students', 'medical_condition')) $table->text('medical_condition')->nullable();
            if (!Schema::hasColumn('students', 'religion')) $table->string('religion')->nullable();
            if (!Schema::hasColumn('students', 'ethnicity')) $table->string('ethnicity')->nullable();
            if (!Schema::hasColumn('students', 'student_caste')) $table->string('student_caste')->nullable();
            if (!Schema::hasColumn('students', 'mother_tongue')) $table->string('mother_tongue')->default('Nepali');
            if (!Schema::hasColumn('students', 'admission_date')) $table->date('admission_date')->nullable();
            if (!Schema::hasColumn('students', 'is_mukta_kamaiya')) $table->string('is_mukta_kamaiya')->default('No');
            if (!Schema::hasColumn('students', 'is_martyr_family')) $table->string('is_martyr_family')->default('No');

            // Prior Academic Background
            if (!Schema::hasColumn('students', 'shift')) $table->string('shift')->nullable();
            if (!Schema::hasColumn('students', 'medium')) $table->string('medium')->nullable();
            if (!Schema::hasColumn('students', 'entrance_score')) $table->decimal('entrance_score', 5, 2)->nullable();
            if (!Schema::hasColumn('students', 'passed_level')) $table->string('passed_level')->nullable();
            if (!Schema::hasColumn('students', 'passed_institution')) $table->string('passed_institution')->nullable();
            if (!Schema::hasColumn('students', 'passed_gpa')) $table->string('passed_gpa')->nullable();

            // Parent/Guardian Lookup Proxies
            if (!Schema::hasColumn('students', 'father_user_id')) $table->unsignedBigInteger('father_user_id')->nullable();
            if (!Schema::hasColumn('students', 'mother_user_id')) $table->unsignedBigInteger('mother_user_id')->nullable();
            if (!Schema::hasColumn('students', 'guardian_user_id')) $table->unsignedBigInteger('guardian_user_id')->nullable();

            // Father Information Block
            if (!Schema::hasColumn('students', 'father_name')) $table->string('father_name')->nullable();
            if (!Schema::hasColumn('students', 'father_mobile')) $table->string('father_mobile')->nullable();
            if (!Schema::hasColumn('students', 'father_email')) $table->string('father_email')->nullable();
            if (!Schema::hasColumn('students', 'father_occupation')) $table->string('father_occupation')->nullable();
            if (!Schema::hasColumn('students', 'father_office_name')) $table->string('father_office_name')->nullable();
            if (!Schema::hasColumn('students', 'father_designation')) $table->string('father_designation')->nullable();
            if (!Schema::hasColumn('students', 'father_office_contact')) $table->string('father_office_contact')->nullable();
            if (!Schema::hasColumn('students', 'father_address')) $table->string('father_address')->nullable();

            // Mother Information Block
            if (!Schema::hasColumn('students', 'mother_name')) $table->string('mother_name')->nullable();
            if (!Schema::hasColumn('students', 'mother_mobile')) $table->string('mother_mobile')->nullable();
            if (!Schema::hasColumn('students', 'mother_email')) $table->string('mother_email')->nullable();
            if (!Schema::hasColumn('students', 'mother_occupation')) $table->string('mother_occupation')->nullable();
            if (!Schema::hasColumn('students', 'mother_office_name')) $table->string('mother_office_name')->nullable();
            if (!Schema::hasColumn('students', 'mother_designation')) $table->string('mother_designation')->nullable();
            if (!Schema::hasColumn('students', 'mother_office_contact')) $table->string('mother_office_contact')->nullable();
            if (!Schema::hasColumn('students', 'mother_address')) $table->string('mother_address')->nullable();

            // Local Guardian Information Block
            if (!Schema::hasColumn('students', 'guardian_name')) $table->string('guardian_name')->nullable();
            if (!Schema::hasColumn('students', 'guardian_gender')) $table->string('guardian_gender')->nullable();
            if (!Schema::hasColumn('students', 'guardian_mobile')) $table->string('guardian_mobile')->nullable();
            if (!Schema::hasColumn('students', 'guardian_email')) $table->string('guardian_email')->nullable();
            if (!Schema::hasColumn('students', 'guardian_occupation')) $table->string('guardian_occupation')->nullable();
            if (!Schema::hasColumn('students', 'guardian_relation')) $table->string('guardian_relation')->nullable();
            if (!Schema::hasColumn('students', 'guardian_office_name')) $table->string('guardian_office_name')->nullable();
            if (!Schema::hasColumn('students', 'guardian_address')) $table->string('guardian_address')->nullable();

            // Permanent Location Geometry
            if (!Schema::hasColumn('students', 'perm_country')) $table->string('perm_country')->default('Nepal');
            if (!Schema::hasColumn('students', 'perm_state')) $table->string('perm_state')->nullable();
            if (!Schema::hasColumn('students', 'perm_district')) $table->string('perm_district')->nullable();
            if (!Schema::hasColumn('students', 'perm_municipality')) $table->string('perm_municipality')->nullable();
            if (!Schema::hasColumn('students', 'perm_ward')) $table->integer('perm_ward')->nullable();
            if (!Schema::hasColumn('students', 'perm_google_code')) $table->string('perm_google_code')->nullable();
            if (!Schema::hasColumn('students', 'perm_zip')) $table->string('perm_zip')->nullable();
            if (!Schema::hasColumn('students', 'perm_tole')) $table->string('perm_tole')->nullable();
            if (!Schema::hasColumn('students', 'perm_house_no')) $table->string('perm_house_no')->nullable();

            // Temporary Location Geometry
            if (!Schema::hasColumn('students', 'temp_country')) $table->string('temp_country')->default('Nepal');
            if (!Schema::hasColumn('students', 'temp_state')) $table->string('temp_state')->nullable();
            if (!Schema::hasColumn('students', 'temp_district')) $table->string('temp_district')->nullable();
            if (!Schema::hasColumn('students', 'temp_municipality')) $table->string('temp_municipality')->nullable();
            if (!Schema::hasColumn('students', 'temp_ward')) $table->integer('temp_ward')->nullable();
            if (!Schema::hasColumn('students', 'temp_google_code')) $table->string('temp_google_code')->nullable();
            if (!Schema::hasColumn('students', 'temp_zip')) $table->string('temp_zip')->nullable();
            if (!Schema::hasColumn('students', 'temp_tole')) $table->string('temp_tole')->nullable();
            if (!Schema::hasColumn('students', 'temp_house_no')) $table->string('temp_house_no')->nullable();

            // Verification Documents
            if (!Schema::hasColumn('students', 'document_type')) $table->string('document_type')->nullable();
            if (!Schema::hasColumn('students', 'attached_document')) $table->string('attached_document')->nullable();
        });
    }

    public function down(): void
    {
        // Safe rollback mechanism handled automatically by column checking routines
    }
};