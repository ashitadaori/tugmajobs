<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration fixes the application status histories table by:
     * 1. Ensuring the table has the correct name (job_application_status_histories)
     * 2. Adding missing columns (old_status, new_status, updated_by)
     */
    public function up(): void
    {
        // First, ensure we have the correct table name
        if (Schema::hasTable('application_status_histories') && !Schema::hasTable('job_application_status_histories')) {
            Schema::rename('application_status_histories', 'job_application_status_histories');
        }

        // Now add missing columns to job_application_status_histories
        if (Schema::hasTable('job_application_status_histories')) {
            Schema::table('job_application_status_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('job_application_status_histories', 'old_status')) {
                    $table->string('old_status')->nullable()->after('job_application_id');
                }
                if (!Schema::hasColumn('job_application_status_histories', 'new_status')) {
                    $table->string('new_status')->nullable()->after('old_status');
                }
                if (!Schema::hasColumn('job_application_status_histories', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
                }
            });
        }

        // Drop the old singular table if it exists (data should have been migrated)
        Schema::dropIfExists('application_status_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('job_application_status_histories')) {
            Schema::table('job_application_status_histories', function (Blueprint $table) {
                // Drop foreign key first
                if (Schema::hasColumn('job_application_status_histories', 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn('job_application_status_histories', 'new_status')) {
                    $table->dropColumn('new_status');
                }
                if (Schema::hasColumn('job_application_status_histories', 'old_status')) {
                    $table->dropColumn('old_status');
                }
            });
        }
    }
};
