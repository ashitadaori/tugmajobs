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
        Schema::create('kyc_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('status'); // approved, completed, verified, failed, etc.
            $table->string('didit_status')->nullable(); // Original status from Didit
            
            // Personal Information from ID Verification
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            
            // Document Information
            $table->string('document_type')->nullable(); // Identity Card, Passport, etc.
            $table->string('document_number')->nullable();
            $table->date('document_issue_date')->nullable();
            $table->date('document_expiration_date')->nullable();
            $table->string('issuing_state')->nullable();
            $table->string('issuing_state_name')->nullable();
            
            // Address Information
            $table->text('address')->nullable();
            $table->text('formatted_address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Verification Scores and Results
            $table->decimal('face_match_score', 5, 2)->nullable();
            $table->string('face_match_status')->nullable();
            $table->decimal('liveness_score', 5, 2)->nullable();
            $table->string('liveness_status')->nullable();
            $table->string('id_verification_status')->nullable();
            $table->string('ip_analysis_status')->nullable();
            $table->decimal('age_estimation', 5, 2)->nullable();
            
            // IP and Device Information
            $table->string('ip_address')->nullable();
            $table->string('ip_country')->nullable();
            $table->string('ip_city')->nullable();
            $table->boolean('is_vpn_or_tor')->default(false);
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('browser_family')->nullable();
            $table->string('os_family')->nullable();
            
            // Image URLs (from Didit)
            $table->text('front_image_url')->nullable();
            $table->text('back_image_url')->nullable();
            $table->text('portrait_image_url')->nullable();
            $table->text('liveness_video_url')->nullable();
            
            // Metadata
            $table->json('raw_payload'); // Complete Didit response for reference
            $table->json('warnings')->nullable(); // Any warnings from verification
            $table->string('verification_method')->default('webhook'); // webhook, api, manual
            $table->timestamp('didit_created_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('session_id');
            $table->index('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_data');
    }
};
