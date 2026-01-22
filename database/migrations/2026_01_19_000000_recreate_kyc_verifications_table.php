<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Recreate the kyc_verifications table that was previously dropped.
     * This table is needed by the admin KYC management system.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kyc_verifications')) {
            Schema::create('kyc_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('session_id')->unique();
                $table->string('status')->default('pending');

                // Document information
                $table->string('document_type')->nullable();
                $table->string('document_number')->nullable();

                // Personal information
                $table->string('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('gender')->nullable();
                $table->text('address')->nullable();
                $table->string('nationality')->nullable();

                // Raw data storage
                $table->json('raw_data')->nullable();
                $table->json('verification_data')->nullable();

                // Timestamps
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['user_id', 'status']);
                $table->index('session_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
