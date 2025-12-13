<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration is skipped because the status column uses integer values.
     * The Job model uses integer constants (0=pending, 1=approved, etc.)
     */
    public function up(): void
    {
        // Skip this migration - status column uses integers, not strings
        // The Job model correctly uses integer constants:
        // STATUS_PENDING = 0
        // STATUS_APPROVED = 1
        // STATUS_REJECTED = 2
        // STATUS_EXPIRED = 3
        // STATUS_CLOSED = 4
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed
    }
};
