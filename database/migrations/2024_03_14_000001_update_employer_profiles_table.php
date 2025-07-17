<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('employer_profiles', 'status')) {
                $table->string('status')->default('draft')->after('social_links');
            }
            
            // Ensure all necessary columns exist
            if (!Schema::hasColumn('employer_profiles', 'company_name')) {
                $table->string('company_name');
            }
            if (!Schema::hasColumn('employer_profiles', 'company_description')) {
                $table->text('company_description')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'industry')) {
                $table->string('industry');
            }
            if (!Schema::hasColumn('employer_profiles', 'company_size')) {
                $table->string('company_size');
            }
            if (!Schema::hasColumn('employer_profiles', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'company_logo')) {
                $table->string('company_logo')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'location')) {
                $table->string('location');
            }
            if (!Schema::hasColumn('employer_profiles', 'social_links')) {
                $table->json('social_links')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'profile_views')) {
                $table->unsignedInteger('profile_views')->default(0);
            }
            
            // New fields for enhanced features
            if (!Schema::hasColumn('employer_profiles', 'company_culture')) {
                $table->json('company_culture')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'benefits_offered')) {
                $table->json('benefits_offered')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'founded_year')) {
                $table->year('founded_year')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'headquarters')) {
                $table->string('headquarters')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'specialties')) {
                $table->json('specialties')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'company_video')) {
                $table->string('company_video')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'gallery_images')) {
                $table->json('gallery_images')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'hiring_process')) {
                $table->json('hiring_process')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'contact_phone')) {
                $table->string('contact_phone')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (!Schema::hasColumn('employer_profiles', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}; 