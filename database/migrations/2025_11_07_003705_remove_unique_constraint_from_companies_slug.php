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
        Schema::table('companies', function (Blueprint $table) {
            // Drop the unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add a regular index instead (for performance)
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Remove the index
            $table->dropIndex(['slug']);
            
            // Add back the unique constraint
            $table->unique('slug');
        });
    }
};
