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
        Schema::table('job_views', function (Blueprint $table) {
            // Change referrer column from VARCHAR(255) to TEXT to handle long URLs
            $table->text('referrer')->nullable()->change();
            
            // Also increase user_agent column size as it can also be long
            $table->text('user_agent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_views', function (Blueprint $table) {
            // Revert back to string (VARCHAR(255))
            $table->string('referrer')->nullable()->change();
            $table->string('user_agent')->nullable()->change();
        });
    }
};