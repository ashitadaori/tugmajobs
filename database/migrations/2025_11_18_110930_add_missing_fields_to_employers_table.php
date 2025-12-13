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
    public function up()
    {
        Schema::table('employers', function (Blueprint $table) {
            // Add JSON columns for additional company information
            if (!Schema::hasColumn('employers', 'company_culture')) {
                $table->json('company_culture')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('employers', 'benefits_offered')) {
                $table->json('benefits_offered')->nullable()->after('company_culture');
            }
            if (!Schema::hasColumn('employers', 'specialties')) {
                $table->json('specialties')->nullable()->after('benefits_offered');
            }
            if (!Schema::hasColumn('employers', 'gallery_images')) {
                $table->json('gallery_images')->nullable()->after('company_logo');
            }
            if (!Schema::hasColumn('employers', 'company_video')) {
                $table->string('company_video')->nullable()->after('gallery_images');
            }
            if (!Schema::hasColumn('employers', 'hiring_process')) {
                $table->json('hiring_process')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('employers', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('specialties');
            }
            if (!Schema::hasColumn('employers', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('employers', 'profile_views')) {
                $table->integer('profile_views')->default(0)->after('total_hires');
            }
            if (!Schema::hasColumn('employers', 'active_jobs')) {
                $table->integer('active_jobs')->default(0)->after('profile_views');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employers', function (Blueprint $table) {
            $table->dropColumn([
                'company_culture',
                'benefits_offered',
                'specialties',
                'gallery_images',
                'company_video',
                'hiring_process',
                'meta_title',
                'meta_description',
                'profile_views',
                'active_jobs'
            ]);
        });
    }
};
