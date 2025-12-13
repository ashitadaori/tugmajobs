<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop the legacy employer_profiles table after migrating data to employers table
     *
     * IMPORTANT: Only run this AFTER confirming data migration is complete
     *
     * @return void
     */
    public function up()
    {
        // Drop the legacy employer_profiles table
        Schema::dropIfExists('employer_profiles');

        echo "✓ Dropped legacy employer_profiles table\n";
        echo "  All employer data is now in the 'employers' table\n";
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This will NOT restore the table with data
     * Only use for development rollback
     *
     * @return void
     */
    public function down()
    {
        // Recreate the employer_profiles table (without data)
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
            $table->string('status')->default('draft');
            $table->json('company_culture')->nullable();
            $table->json('benefits_offered')->nullable();
            $table->integer('total_jobs_posted')->default(0);
            $table->integer('active_jobs')->default(0);
            $table->integer('total_applications_received')->default(0);
            $table->integer('profile_views')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('founded_year')->nullable();
            $table->string('headquarters')->nullable();
            $table->json('specialties')->nullable();
            $table->string('company_video')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('hiring_process')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        echo "⚠ WARNING: Table structure recreated but data was NOT restored\n";
        echo "  You must restore data from backup if needed\n";
    }
};
