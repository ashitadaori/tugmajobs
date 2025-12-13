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
        Schema::table('jobs', function (Blueprint $table) {
            // Add flag to identify admin-posted jobs
            $table->boolean('posted_by_admin')->default(false)->after('employer_id');
            
            // Add performance indexes for frequently queried columns
            $table->index('status', 'idx_jobs_status');
            $table->index('category_id', 'idx_jobs_category');
            $table->index('job_type_id', 'idx_jobs_type');
            $table->index('location', 'idx_jobs_location');
            $table->index('created_at', 'idx_jobs_created');
            $table->index('featured', 'idx_jobs_featured');
            $table->index(['status', 'created_at'], 'idx_jobs_status_created');
            $table->index(['category_id', 'status'], 'idx_jobs_category_status');
            
            // Add full-text index for search optimization
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE jobs ADD FULLTEXT INDEX idx_jobs_search (title, description)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_jobs_status');
            $table->dropIndex('idx_jobs_category');
            $table->dropIndex('idx_jobs_type');
            $table->dropIndex('idx_jobs_location');
            $table->dropIndex('idx_jobs_created');
            $table->dropIndex('idx_jobs_featured');
            $table->dropIndex('idx_jobs_status_created');
            $table->dropIndex('idx_jobs_category_status');
            
            // Drop full-text index
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE jobs DROP INDEX idx_jobs_search');
            }
            
            // Drop column
            $table->dropColumn('posted_by_admin');
        });
    }
};
