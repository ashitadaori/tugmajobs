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
        Schema::create('jobseekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Personal Information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('marital_status')->nullable();
            
            // Contact Information
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('github_url')->nullable();
            
            // Address Information
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Philippines');
            $table->string('postal_code')->nullable();
            
            // Professional Information
            $table->string('current_job_title')->nullable();
            $table->string('current_company')->nullable();
            $table->text('professional_summary')->nullable();
            $table->integer('total_experience_years')->default(0);
            $table->integer('total_experience_months')->default(0);
            
            // Skills and Expertise
            $table->json('skills')->nullable(); // Technical skills
            $table->json('soft_skills')->nullable(); // Communication, leadership, etc.
            $table->json('languages')->nullable(); // Language proficiency
            $table->json('certifications')->nullable(); // Professional certifications
            
            // Education
            $table->json('education')->nullable(); // Educational background
            $table->json('courses')->nullable(); // Additional courses/training
            
            // Experience
            $table->json('work_experience')->nullable(); // Work history
            $table->json('projects')->nullable(); // Personal/professional projects
            
            // Job Preferences
            $table->json('preferred_job_types')->nullable(); // Full-time, Part-time, Contract, etc.
            $table->json('preferred_categories')->nullable(); // IT, Marketing, Sales, etc.
            $table->json('preferred_locations')->nullable(); // Preferred work locations
            $table->boolean('open_to_remote')->default(false);
            $table->boolean('open_to_relocation')->default(false);
            $table->decimal('expected_salary_min', 10, 2)->nullable();
            $table->decimal('expected_salary_max', 10, 2)->nullable();
            $table->string('salary_currency')->default('PHP');
            $table->enum('salary_period', ['hourly', 'daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            
            // Availability
            $table->enum('availability', ['immediate', '1_week', '2_weeks', '1_month', '2_months', '3_months'])->default('immediate');
            $table->date('available_from')->nullable();
            $table->boolean('currently_employed')->default(false);
            $table->integer('notice_period_days')->nullable();
            
            // Documents and Media
            $table->string('resume_file')->nullable(); // CV/Resume file path
            $table->string('cover_letter_file')->nullable(); // Cover letter file path
            $table->string('profile_photo')->nullable(); // Profile photo path
            $table->json('portfolio_files')->nullable(); // Portfolio documents/images
            
            // Preferences and Settings
            $table->json('notification_preferences')->nullable();
            $table->json('privacy_settings')->nullable();
            $table->boolean('profile_visibility')->default(true); // Visible to employers
            $table->boolean('allow_recruiter_contact')->default(true);
            $table->json('job_alert_preferences')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            
            // Status and Verification
            $table->enum('profile_status', ['incomplete', 'complete', 'verified', 'suspended'])->default('incomplete');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->timestamp('premium_expires_at')->nullable();
            $table->decimal('profile_completion_percentage', 5, 2)->default(0.00);
            
            // Statistics
            $table->integer('profile_views')->default(0);
            $table->integer('total_applications')->default(0);
            $table->integer('interviews_attended')->default(0);
            $table->integer('jobs_offered')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable(); // Employer ratings
            
            // SEO and Search
            $table->text('search_keywords')->nullable(); // For internal search optimization
            $table->decimal('search_score', 8, 2)->default(0.00); // Calculated search ranking
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'profile_status']);
            $table->index('profile_visibility');
            $table->index('currently_employed');
            $table->index('expected_salary_min');
            $table->index('expected_salary_max');
            $table->index(['city', 'state']);
            $table->index('total_experience_years');
            $table->index('availability');
            $table->index('profile_completion_percentage');
            $table->index('search_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobseekers');
    }
};
