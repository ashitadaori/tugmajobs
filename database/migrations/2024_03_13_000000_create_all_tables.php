<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Create base tables without foreign keys
        
        // Create users table first
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('mobile')->nullable();
            $table->string('designation')->nullable();
            $table->string('image')->nullable();
            $table->enum('role', ['superadmin', 'admin', 'employer', 'jobseeker'])->default('jobseeker');
            $table->json('skills')->nullable();
            $table->text('education')->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('bio')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('verification_document')->nullable();
            $table->json('preferred_job_types')->nullable();
            $table->json('preferred_categories')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('preferred_salary_range')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('notification_preferences')->nullable();
            $table->json('privacy_settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create job_types table
        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add password_resets table
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 2. Create pivot tables with foreign keys
        
        // Create role_permissions pivot table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Create user_roles pivot table
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Create profile and related tables
        
        // Create employer_profiles table
        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->text('company_description')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('location')->nullable();
            $table->json('social_links')->nullable();
            $table->json('company_culture')->nullable();
            $table->json('benefits_offered')->nullable();
            $table->integer('total_jobs_posted')->default(0);
            $table->integer('active_jobs')->default(0);
            $table->integer('total_applications_received')->default(0);
            $table->integer('profile_views')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create job_seeker_profiles table
        Schema::create('job_seeker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('skills')->nullable();
            $table->text('experience')->nullable();
            $table->json('education')->nullable();
            $table->decimal('current_salary', 10, 2)->nullable();
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('resume_file')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_kyc_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_type_id')->constrained('job_types')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('location');
            $table->boolean('status')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamp('deadline')->nullable();
            $table->integer('views')->default(0);
            $table->string('source')->nullable();
            $table->json('meta_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Create job_applications table
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->text('cover_letter')->nullable();
            $table->string('resume')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create saved_jobs table
        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Create kyc_documents table
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('document_number');
            $table->string('document_file');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('saved_jobs');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_seeker_profiles');
        Schema::dropIfExists('employer_profiles');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('job_types');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_resets');
    }
};
