<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds columns to store content-analyzed category information,
     * enabling dual matching based on both employer's selection and actual content.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Store inferred categories from content analysis
            // JSON format: {"administrative_clerical": {"score": 0.85, "confidence": "high"}, ...}
            $table->json('inferred_categories')->nullable()->after('category_id');

            // Primary inferred category (the highest scored one)
            $table->string('primary_inferred_category', 100)->nullable()->after('inferred_categories');

            // Score of the primary inferred category (0.0 to 1.0)
            $table->decimal('primary_inferred_score', 5, 4)->nullable()->after('primary_inferred_category');

            // Flag indicating if there's a mismatch between employer's category and content
            $table->boolean('has_category_mismatch')->default(false)->after('primary_inferred_score');

            // Skills extracted from job content
            // JSON array: ["php", "javascript", "data entry", "excel", ...]
            $table->json('extracted_skills')->nullable()->after('has_category_mismatch');

            // Role type detected from content (technical, administrative, customer_facing, etc.)
            $table->string('detected_role_type', 50)->nullable()->after('extracted_skills');

            // Timestamp when content was last analyzed
            $table->timestamp('content_analyzed_at')->nullable()->after('detected_role_type');

            // Add index for faster queries on inferred categories
            $table->index('primary_inferred_category', 'idx_jobs_primary_inferred_category');
            $table->index('has_category_mismatch', 'idx_jobs_has_category_mismatch');
            $table->index('detected_role_type', 'idx_jobs_detected_role_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_jobs_primary_inferred_category');
            $table->dropIndex('idx_jobs_has_category_mismatch');
            $table->dropIndex('idx_jobs_detected_role_type');

            // Drop columns
            $table->dropColumn([
                'inferred_categories',
                'primary_inferred_category',
                'primary_inferred_score',
                'has_category_mismatch',
                'extracted_skills',
                'detected_role_type',
                'content_analyzed_at'
            ]);
        });
    }
};
