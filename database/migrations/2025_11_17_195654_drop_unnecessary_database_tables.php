<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop unnecessary tables that are not used in the application:
     * - industries: Not referenced, employers use free-text industry field
     * - company_sizes: Not referenced, employers use string company_size field
     * - locations: Minimal usage, Mapbox integration used instead
     * - job_categories: Duplicates categories table functionality
     * - job_skills: Not referenced, skills stored as JSON in profiles
     * - job_user: Pivot table never used, saved_jobs table used instead
     * - personal_access_tokens: Laravel Sanctum table not used for API auth
     *
     * @return void
     */
    public function up()
    {
        // Drop unused reference/lookup tables
        Schema::dropIfExists('industries');
        Schema::dropIfExists('company_sizes');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('job_categories');
        Schema::dropIfExists('job_skills');

        // Drop unused pivot table
        Schema::dropIfExists('job_user');

        // Drop unused authentication table
        Schema::dropIfExists('personal_access_tokens');
    }

    /**
     * Reverse the migrations.
     *
     * Note: This down() method recreates the tables with minimal structure.
     * Original seeded data will not be restored.
     *
     * @return void
     */
    public function down()
    {
        // Recreate industries table
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate company_sizes table
        Schema::create('company_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('range');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Recreate locations table
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('province');
            $table->string('region');
            $table->string('country')->default('Philippines');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        // Recreate job_categories table
        Schema::create('job_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Recreate job_skills table
        Schema::create('job_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->timestamps();
        });

        // Recreate job_user pivot table
        Schema::create('job_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
        });

        // Recreate personal_access_tokens table
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
};
