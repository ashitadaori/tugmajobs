<?php

namespace App\Observers;

use App\Models\Job;
use App\Services\JobContentAnalyzerService;
use Illuminate\Support\Facades\Log;

/**
 * Job Observer
 *
 * Automatically analyzes job content when jobs are created or updated.
 * This ensures all jobs have inferred categories for enhanced K-means matching.
 */
class JobObserver
{
    protected JobContentAnalyzerService $analyzer;

    public function __construct()
    {
        $this->analyzer = new JobContentAnalyzerService();
    }

    /**
     * Handle the Job "created" event.
     */
    public function created(Job $job): void
    {
        $this->analyzeJobContent($job, 'created');
    }

    /**
     * Handle the Job "updated" event.
     */
    public function updated(Job $job): void
    {
        // Only re-analyze if content-related fields changed
        $contentFields = ['title', 'description', 'requirements', 'benefits', 'category_id'];
        $changedFields = array_keys($job->getChanges());

        $contentChanged = !empty(array_intersect($contentFields, $changedFields));

        if ($contentChanged) {
            $this->analyzeJobContent($job, 'updated');
        }
    }

    /**
     * Handle the Job "restored" event.
     */
    public function restored(Job $job): void
    {
        // Re-analyze when job is restored from soft delete
        if (!$job->content_analyzed_at) {
            $this->analyzeJobContent($job, 'restored');
        }
    }

    /**
     * Analyze job content
     */
    protected function analyzeJobContent(Job $job, string $event): void
    {
        try {
            // Use queue for background processing if available
            // For now, analyze synchronously
            $this->analyzer->analyzeJob($job);

            Log::info("Job content analyzed on {$event}", [
                'job_id' => $job->id,
                'title' => $job->title,
                'primary_inferred_category' => $job->primary_inferred_category,
                'has_mismatch' => $job->has_category_mismatch
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to analyze job content on {$event}", [
                'job_id' => $job->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
