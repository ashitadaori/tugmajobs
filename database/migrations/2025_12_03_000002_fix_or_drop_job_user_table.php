<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The job_user pivot table was created without foreign keys, making it useless.
     * The saved_jobs table is already used for saving jobs, so job_user is redundant.
     * This migration drops the incomplete job_user table if it still exists.
     */
    public function up(): void
    {
        // Drop the incomplete job_user pivot table
        // The saved_jobs table already handles the user-job relationship for saved jobs
        Schema::dropIfExists('job_user');
    }

    /**
     * Reverse the migrations.
     *
     * Note: We recreate it with proper structure in case someone needs to restore
     */
    public function down(): void
    {
        if (!Schema::hasTable('job_user')) {
            Schema::create('job_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['job_id', 'user_id']);
            });
        }
    }
};
