<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Job Content Analyzer Service
 *
 * Analyzes job postings and updates them with inferred categories,
 * extracted skills, and detected role types.
 *
 * This service should be run:
 * 1. When a new job is posted (via observer or event)
 * 2. When a job is updated
 * 3. Periodically via scheduled command to refresh all jobs
 */
class JobContentAnalyzerService
{
    protected ContentAnalysisService $contentAnalysis;

    public function __construct()
    {
        $this->contentAnalysis = new ContentAnalysisService();
    }

    /**
     * Analyze a single job and update its content-based fields
     *
     * @param Job $job
     * @param bool $save Whether to save the job after analysis
     * @return Job
     */
    public function analyzeJob(Job $job, bool $save = true): Job
    {
        try {
            // Infer categories from content
            $inferredCategories = $this->contentAnalysis->inferJobCategories($job);

            // Get top category
            $topCategory = array_slice($inferredCategories, 0, 1, true);
            $topKey = array_key_first($topCategory);
            $topData = $topCategory[$topKey] ?? null;

            // Extract skills
            $extractedSkills = $this->contentAnalysis->extractJobSkills($job);
            $skillNames = array_keys($extractedSkills);

            // Detect role type
            $detectedRoleType = $this->detectPrimaryRoleType($job);

            // Detect category mismatch
            $mismatch = $this->contentAnalysis->detectCategoryMismatch($job);

            // Update job fields
            $job->inferred_categories = $this->formatInferredCategories($inferredCategories);
            $job->primary_inferred_category = $topKey;
            $job->primary_inferred_score = $topData['score'] ?? 0;
            $job->has_category_mismatch = $mismatch['has_mismatch'];
            $job->extracted_skills = $skillNames;
            $job->detected_role_type = $detectedRoleType;
            $job->content_analyzed_at = now();

            if ($save) {
                $job->save();
            }

            return $job;

        } catch (\Exception $e) {
            Log::error("Failed to analyze job {$job->id}: " . $e->getMessage());
            return $job;
        }
    }

    /**
     * Analyze multiple jobs
     *
     * @param Collection|array $jobs
     * @return array Results with success/failure counts
     */
    public function analyzeJobs($jobs): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'mismatches_found' => 0
        ];

        foreach ($jobs as $job) {
            $results['total']++;

            try {
                $analyzedJob = $this->analyzeJob($job);

                if ($analyzedJob->content_analyzed_at) {
                    $results['success']++;

                    if ($analyzedJob->has_category_mismatch) {
                        $results['mismatches_found']++;
                    }
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Failed to analyze job {$job->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Analyze all active jobs
     *
     * @return array
     */
    public function analyzeAllActiveJobs(): array
    {
        $jobs = Job::where('status', 1)->get();
        return $this->analyzeJobs($jobs);
    }

    /**
     * Analyze jobs that haven't been analyzed yet
     *
     * @return array
     */
    public function analyzeUnanalyzedJobs(): array
    {
        $jobs = Job::where('status', 1)
            ->whereNull('content_analyzed_at')
            ->get();

        return $this->analyzeJobs($jobs);
    }

    /**
     * Re-analyze jobs that were analyzed more than X hours ago
     *
     * @param int $hours
     * @return array
     */
    public function reanalyzeStaleJobs(int $hours = 24): array
    {
        $jobs = Job::where('status', 1)
            ->where(function($query) use ($hours) {
                $query->whereNull('content_analyzed_at')
                    ->orWhere('content_analyzed_at', '<', now()->subHours($hours));
            })
            ->get();

        return $this->analyzeJobs($jobs);
    }

    /**
     * Get jobs with category mismatches
     *
     * @param int $limit
     * @return Collection
     */
    public function getJobsWithMismatches(int $limit = 50): Collection
    {
        return Job::where('status', 1)
            ->where('has_category_mismatch', true)
            ->with(['category', 'employer.employerProfile'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'employer_category' => $job->category->name ?? 'Unknown',
                    'employer_category_id' => $job->category_id,
                    'inferred_category' => $job->primary_inferred_category,
                    'inferred_score' => $job->primary_inferred_score,
                    'extracted_skills' => array_slice($job->extracted_skills ?? [], 0, 5),
                    'detected_role_type' => $job->detected_role_type,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s')
                ];
            });
    }

    /**
     * Get mismatch statistics
     *
     * @return array
     */
    public function getMismatchStatistics(): array
    {
        $totalJobs = Job::where('status', 1)->count();
        $analyzedJobs = Job::where('status', 1)
            ->whereNotNull('content_analyzed_at')
            ->count();
        $mismatchedJobs = Job::where('status', 1)
            ->where('has_category_mismatch', true)
            ->count();

        // Get mismatch breakdown by category
        $mismatchByCategory = Job::where('status', 1)
            ->where('has_category_mismatch', true)
            ->selectRaw('category_id, primary_inferred_category, COUNT(*) as count')
            ->groupBy('category_id', 'primary_inferred_category')
            ->get()
            ->map(function($item) {
                return [
                    'employer_category_id' => $item->category_id,
                    'inferred_category' => $item->primary_inferred_category,
                    'count' => $item->count
                ];
            });

        return [
            'total_jobs' => $totalJobs,
            'analyzed_jobs' => $analyzedJobs,
            'unanalyzed_jobs' => $totalJobs - $analyzedJobs,
            'mismatched_jobs' => $mismatchedJobs,
            'mismatch_rate' => $analyzedJobs > 0
                ? round(($mismatchedJobs / $analyzedJobs) * 100, 2)
                : 0,
            'breakdown_by_category' => $mismatchByCategory
        ];
    }

    /**
     * Detect primary role type from job content
     */
    protected function detectPrimaryRoleType(Job $job): ?string
    {
        $text = strtolower($job->title . ' ' . $job->description . ' ' . $job->requirements);

        $roleTypes = [
            'technical' => ['developer', 'engineer', 'programmer', 'analyst', 'architect', 'devops'],
            'administrative' => ['clerk', 'secretary', 'assistant', 'receptionist', 'encoder', 'admin'],
            'customer_facing' => ['customer service', 'sales', 'support', 'representative', 'agent'],
            'creative' => ['designer', 'artist', 'creative', 'editor', 'photographer'],
            'management' => ['manager', 'supervisor', 'lead', 'head', 'director'],
            'manual_labor' => ['worker', 'operator', 'driver', 'helper', 'laborer', 'technician']
        ];

        $scores = [];

        foreach ($roleTypes as $type => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$type] = $score;
        }

        arsort($scores);
        $topType = array_key_first($scores);

        return $scores[$topType] > 0 ? $topType : null;
    }

    /**
     * Format inferred categories for storage (top 5 only)
     */
    protected function formatInferredCategories(array $inferredCategories): array
    {
        $formatted = [];
        $count = 0;

        foreach ($inferredCategories as $key => $data) {
            if ($count >= 5) break;

            $formatted[$key] = [
                'score' => $data['score'],
                'confidence' => $data['confidence'],
                'matched_roles' => array_slice($data['matched_roles'] ?? [], 0, 3),
                'matched_skills' => array_slice($data['matched_skills'] ?? [], 0, 5)
            ];

            $count++;
        }

        return $formatted;
    }
}
