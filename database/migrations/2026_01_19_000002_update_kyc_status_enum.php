<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'pending_review' to the enum allowed values
        // We use raw SQL because modifying enums via Schema builder is not fully supported/reliable in all drivers
        DB::statement("ALTER TABLE users MODIFY COLUMN kyc_status ENUM('pending', 'in_progress', 'verified', 'failed', 'expired', 'pending_review') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        // Note: This might fail if there are rows with 'pending_review' status.
        // In a real production scenario, we might want to update those rows first.

        DB::table('users')->where('kyc_status', 'pending_review')->update(['kyc_status' => 'pending']);

        DB::statement("ALTER TABLE users MODIFY COLUMN kyc_status ENUM('pending', 'in_progress', 'verified', 'failed', 'expired') NOT NULL DEFAULT 'pending'");
    }
};
