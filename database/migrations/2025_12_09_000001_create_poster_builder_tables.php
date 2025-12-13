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
        // Poster Templates table - stores the 3 template designs
        Schema::create('poster_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Blue Megaphone", "Yellow Attention", "Modern Hiring"
            $table->string('slug')->unique(); // e.g., "blue-megaphone", "yellow-attention", "modern-hiring"
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable(); // Path to template preview image
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0); // For rotation order
            $table->timestamps();
        });

        // Posters table - stores created posters by admin
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('poster_templates')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin who created
            $table->string('job_title'); // The job position title
            $table->text('requirements'); // Job requirements
            $table->string('company_name'); // Company name
            $table->timestamps();
        });

        // Track the last used template for rotation
        Schema::create('poster_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poster_settings');
        Schema::dropIfExists('posters');
        Schema::dropIfExists('poster_templates');
    }
};
