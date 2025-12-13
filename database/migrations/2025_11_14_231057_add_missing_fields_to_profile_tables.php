<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add missing fields to jobseekers and employers tables
     * Add soft deletes for data safety
     *
     * @return void
     */
    public function up()
    {
        // Add missing fields to jobseekers table
        Schema::table('jobseekers', function (Blueprint $table) {
            // Add current salary fields (from users.salary)
            $table->decimal('current_salary', 10, 2)->nullable()->after('professional_summary');
            $table->string('current_salary_currency', 10)->default('PHP')->after('current_salary');

            // Add soft deletes for data safety
            $table->softDeletes();
        });

        // Add missing fields to employers table
        Schema::table('employers', function (Blueprint $table) {
            // Fields from employer_profiles that are missing
            $table->json('gallery_images')->nullable()->after('company_logo');
            $table->string('company_video')->nullable()->after('gallery_images');
            $table->json('hiring_process')->nullable()->after('settings');
            $table->json('company_culture')->nullable()->after('hiring_process');
            $table->json('benefits_offered')->nullable()->after('company_culture');
            $table->json('specialties')->nullable()->after('benefits_offered');

            // SEO fields
            $table->string('meta_title')->nullable()->after('specialties');
            $table->text('meta_description')->nullable()->after('meta_title');

            // Stats fields
            $table->integer('profile_views')->default(0)->after('total_hires');
            $table->integer('active_jobs')->default(0)->after('profile_views');

            // Add soft deletes for data safety
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobseekers', function (Blueprint $table) {
            $table->dropColumn([
                'current_salary',
                'current_salary_currency',
            ]);
            $table->dropSoftDeletes();
        });

        Schema::table('employers', function (Blueprint $table) {
            $table->dropColumn([
                'gallery_images',
                'company_video',
                'hiring_process',
                'company_culture',
                'benefits_offered',
                'specialties',
                'meta_title',
                'meta_description',
                'profile_views',
                'active_jobs',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
