<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();

            $table->string('organization_name')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('organization_code')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('municipality')->nullable();
            $table->string('ward_number')->nullable();
            $table->string('street_address')->nullable();
            $table->string('google_plus_code')->nullable();
            $table->string('postcode')->nullable();

            $table->string('contact_number')->nullable();
            $table->string('short_address')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('school_slogan')->nullable();
            $table->date('established_date')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();

            $table->string('facebook_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('student_app_link')->nullable();
            $table->string('teacher_app_link')->nullable();
            $table->string('guardian_app_link')->nullable();
            $table->string('youtube_link')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('principal_sign_path')->nullable();
            $table->string('seal_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};