<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add KYC fields if they don't exist
            if (!Schema::hasColumn('users', 'kyc_status')) {
                $table->enum('kyc_status', ['pending', 'in_progress', 'verified', 'failed', 'expired'])
                      ->default('pending')
                      ->after('is_active');
            }
            
            if (!Schema::hasColumn('users', 'kyc_session_id')) {
                $table->string('kyc_session_id')->nullable()->after('kyc_status');
            }
            
            if (!Schema::hasColumn('users', 'kyc_completed_at')) {
                $table->timestamp('kyc_completed_at')->nullable()->after('kyc_session_id');
            }
            
            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable()->after('kyc_completed_at');
            }
            
            if (!Schema::hasColumn('users', 'kyc_data')) {
                $table->json('kyc_data')->nullable()->after('kyc_verified_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_session_id', 
                'kyc_completed_at',
                'kyc_verified_at',
                'kyc_data'
            ]);
        });
    }
};