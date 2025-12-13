<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove redundant and unused fields from users table.
     * These fields have been migrated to profile tables (jobseekers/employers)
     * or are no longer used.
     *
     * IMPORTANT: Run this AFTER migrating data to profile tables
     *
     * @return void
     */
    public function up()
    {
        // First, drop foreign key constraint on parent_id if it exists
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign('users_parent_id_foreign');
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });

        // Now drop the columns
        Schema::table('users', function (Blueprint $table) {
            // Job seeker specific fields (now in jobseekers table)
            $table->dropColumn([
                // Skills & Experience
                'skills',
                'education',
                'experience_years',
                'designation',
                'job_title',
                'bio',
                'qualification',
                'language',
                'categories',

                // Job Preferences
                'preferred_job_types',
                'preferred_categories',
                'preferred_location',
                'preferred_salary_range',
                'experience_level',
                'salary_expectation_min',
                'salary_expectation_max',

                // Salary
                'salary',
                'salary_type',

                // Location & Address
                'address',
                'location',

                // Documents
                'resume',

                // Contact (keeping 'phone' as it's used by all roles)
                'mobile', // Duplicate of phone

                // Unused/Redundant fields
                'parent_id',
                'is_verified', // Redundant with kyc_status
                'verification_document', // Replaced by KYC system
                'kyc_inquiry_id', // Not used, only kyc_session_id is used
                'two_factor_enabled', // 2FA not implemented
                'two_factor_secret', // 2FA not implemented
            ]);
        });

        echo "✓ Removed 30 redundant fields from users table\n";
        echo "  Users table now contains only authentication and shared fields\n";
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This will re-add columns but data will be lost
     * Only use this for development rollback
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add job seeker fields
            $table->json('skills')->nullable();
            $table->text('education')->nullable();
            $table->integer('experience_years')->nullable();
            $table->string('designation')->nullable();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->string('qualification')->nullable();
            $table->string('language')->nullable();
            $table->string('categories')->nullable();

            // Re-add preference fields
            $table->json('preferred_job_types')->nullable();
            $table->json('preferred_categories')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('preferred_salary_range')->nullable();
            $table->string('experience_level')->nullable();
            $table->decimal('salary_expectation_min', 10, 2)->nullable();
            $table->decimal('salary_expectation_max', 10, 2)->nullable();

            // Re-add salary fields
            $table->string('salary')->nullable();
            $table->string('salary_type')->nullable();

            // Re-add location fields
            $table->string('address')->nullable();
            $table->string('location')->nullable();

            // Re-add documents
            $table->string('resume')->nullable();

            // Re-add contact
            $table->string('mobile')->nullable();

            // Re-add unused fields
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('verification_document')->nullable();
            $table->string('kyc_inquiry_id')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
        });

        echo "⚠ WARNING: Columns restored but data was NOT recovered\n";
        echo "  You must restore data from backup if needed\n";
    }
};
