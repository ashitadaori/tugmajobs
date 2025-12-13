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
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Company Information
            $table->string('company_name');
            $table->string('company_slug')->unique()->nullable();
            $table->text('company_description')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_size')->nullable(); // 1-10, 11-50, 51-200, etc.
            $table->string('industry')->nullable();
            $table->year('founded_year')->nullable();
            
            // Contact Information
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_designation')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_phone')->nullable();
            
            // Address Information
            $table->text('business_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Philippines');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Business Registration
            $table->string('business_registration_number')->nullable();
            $table->string('tax_identification_number')->nullable();
            $table->json('business_documents')->nullable(); // Registration docs, etc.
            
            // Social Media & Online Presence
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            
            // Subscription & Plan Information
            $table->string('subscription_plan')->default('free'); // free, basic, premium, enterprise
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->integer('job_posts_limit')->default(5);
            $table->integer('job_posts_used')->default(0);
            
            // Status and Verification
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Preferences
            $table->json('notification_preferences')->nullable();
            $table->json('settings')->nullable();
            
            // Statistics
            $table->integer('total_jobs_posted')->default(0);
            $table->integer('total_applications_received')->default(0);
            $table->integer('total_hires')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_verified']);
            $table->index('company_name');
            $table->index('industry');
            $table->index('subscription_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
