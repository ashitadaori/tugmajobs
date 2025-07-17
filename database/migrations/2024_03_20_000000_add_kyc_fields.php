<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->default('pending');
            $table->string('kyc_inquiry_id')->nullable();
            $table->timestamp('kyc_completed_at')->nullable();
        });

        // We'll use the existing is_verified column for KYC verification in employer_profiles
        // No need to add or rename columns since we can use the existing one
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kyc_status', 'kyc_inquiry_id', 'kyc_completed_at']);
        });
    }
}; 