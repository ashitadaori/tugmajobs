<?php

namespace App\Http\Controllers;

use App\Services\EnhancedKMeansClusteringService;
use App\Services\ContentAnalysisService;
use App\Services\JobContentAnalyzerService;
use App\Services\SkillGapAnalysisService;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Enhanced Recommendation Controller
 *
 * Provides API endpoints for:
 * 1. Enhanced job recommendations using content-aware K-means
 * 2. Job content analysis and category inference
 * 3. Category mismatch detection
 * 4. Skill gap analysis
 */
class EnhancedRecommendationController extends Controller
{
    protected EnhancedKMeansClusteringService $clusteringService;
    protected ContentAnalysisService $contentAnalysis;
    protected JobContentAnalyzerService $jobAnalyzer;
    protected SkillGapAnalysisService $skillGapAnalysis;

    public function __construct()
    {
        $this->clusteringService = new EnhancedKMeansClusteringService();
        $this->contentAnalysis = new ContentAnalysisService();
        $this->jobAnalyzer = new JobContentAnalyzerService();
        $this->skillGapAnalysis = new SkillGapAnalysisService();
    }

    /**
     * Get enhanced job recommendations for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecommendations(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $limit = $request->input('limit', 10);
        $limit = min(max($limit, 1), 50); // Clamp between 1 and 50

        try {
            $recommendations = $this->clusteringService->getEnhancedJobRecommendations(
                $user->id,
                $limit
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => $recommendations->map(function($job) {
                        return [
                            'id' => $job->id,
                            'title' => $job->title,
                            'company' => $job->employer->employerProfile->company_name ?? 'Unknown',
                            'location' => $job->location,
                            'salary_range' => $job->salary_range,
                            'job_type' => $job->jobType->name ?? 'Unknown',
                            'category' => $job->category->name ?? 'Unknown',
                            'is_remote' => $job->is_remote ?? false,
                            'created_at' => $job->created_at->format('Y-m-d'),

                            // Enhanced matching data
                            'enhanced_score' => $job->enhanced_score ?? 0,
                            'match_reasons' => $job->match_reasons ?? [],
                            'skill_match' => $job->skill_match ?? null,
                            'inferred_categories' => $job->inferred_categories ?? [],
                            'category_mismatch' => $job->category_mismatch ?? null
                        ];
                    }),
                    'total' => $recommendations->count(),
                    'algorithm' => 'enhanced_kmeans_content_aware'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze a specific job's content
     *
     * @param int $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeJob(int $jobId)
    {
        $job = Job::with(['category', 'jobType', 'employer.employerProfile'])->find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        try {
            // Infer categories
            $inferredCategories = $this->contentAnalysis->inferJobCategories($job);

            // Extract skills
            $extractedSkills = $this->contentAnalysis->extractJobSkills($job);

            // Extract roles
            $extractedRoles = $this->contentAnalysis->extractJobRoles($job);

            // Detect mismatch
            $mismatch = $this->contentAnalysis->detectCategoryMismatch($job);

            return response()->json([
                'success' => true,
                'data' => [
                    'job' => [
                        'id' => $job->id,
                        'title' => $job->title,
                        'employer_category' => $job->category->name ?? 'Unknown',
                        'employer_category_id' => $job->category_id
                    ],
                    'content_analysis' => [
                        'inferred_categories' => array_slice($inferredCategories, 0, 5, true),
                        'extracted_skills' => array_slice($extractedSkills, 0, 15, true),
                        'extracted_roles' => $extractedRoles,
                        'category_mismatch' => $mismatch
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get skill match between user and job
     *
     * @param Request $request
     * @param int $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkillMatch(Request $request, int $jobId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $job = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        try {
            $skillMatch = $this->contentAnalysis->calculateSkillMatch($user, $job);

            return response()->json([
                'success' => true,
                'data' => [
                    'job_id' => $jobId,
                    'job_title' => $job->title,
                    'skill_match' => $skillMatch,
                    'recommendations' => $this->generateSkillRecommendations($skillMatch)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate skill match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run clustering analysis
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function runClustering(Request $request)
    {
        $k = $request->input('k', 5);
        $k = min(max($k, 2), 15); // Clamp between 2 and 15

        try {
            $result = $this->clusteringService->runEnhancedJobClustering($k);

            return response()->json([
                'success' => true,
                'data' => [
                    'clusters' => array_map(function($cluster) {
                        return [
                            'size' => count($cluster),
                            'job_ids' => array_column($cluster, 'job_id')
                        ];
                    }, $result['clusters'] ?? []),
                    'metrics' => $result['metrics'] ?? [],
                    'k' => $k
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run clustering',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find optimal K value
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findOptimalK(Request $request)
    {
        $maxK = $request->input('max_k', 10);
        $maxK = min(max($maxK, 3), 15);

        try {
            $result = $this->clusteringService->findOptimalK($maxK);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find optimal K',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get labor market insights
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMarketInsights()
    {
        try {
            $insights = $this->clusteringService->getLaborMarketInsights();

            return response()->json([
                'success' => true,
                'data' => $insights
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get market insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get jobs with category mismatches (admin endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryMismatches(Request $request)
    {
        $limit = $request->input('limit', 50);

        try {
            $mismatches = $this->jobAnalyzer->getJobsWithMismatches($limit);
            $statistics = $this->jobAnalyzer->getMismatchStatistics();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'mismatched_jobs' => $mismatches
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get category mismatches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger job content analysis (admin endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerJobAnalysis(Request $request)
    {
        $mode = $request->input('mode', 'unanalyzed'); // unanalyzed, all, stale

        try {
            $results = match($mode) {
                'all' => $this->jobAnalyzer->analyzeAllActiveJobs(),
                'stale' => $this->jobAnalyzer->reanalyzeStaleJobs(24),
                default => $this->jobAnalyzer->analyzeUnanalyzedJobs()
            };

            return response()->json([
                'success' => true,
                'data' => [
                    'mode' => $mode,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze a single job and update database (admin endpoint)
     *
     * @param int $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeAndUpdateJob(int $jobId)
    {
        $job = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        try {
            $analyzedJob = $this->jobAnalyzer->analyzeJob($job);

            return response()->json([
                'success' => true,
                'data' => [
                    'job_id' => $analyzedJob->id,
                    'inferred_categories' => $analyzedJob->inferred_categories,
                    'primary_inferred_category' => $analyzedJob->primary_inferred_category,
                    'primary_inferred_score' => $analyzedJob->primary_inferred_score,
                    'has_category_mismatch' => $analyzedJob->has_category_mismatch,
                    'extracted_skills' => $analyzedJob->extracted_skills,
                    'detected_role_type' => $analyzedJob->detected_role_type,
                    'content_analyzed_at' => $analyzedJob->content_analyzed_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear recommendation caches
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            $this->clusteringService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate skill recommendations based on skill match
     */
    protected function generateSkillRecommendations(array $skillMatch): array
    {
        $recommendations = [];

        if ($skillMatch['score'] >= 0.8) {
            $recommendations[] = [
                'type' => 'excellent_match',
                'message' => 'Your skills are an excellent match for this job!'
            ];
        } elseif ($skillMatch['score'] >= 0.5) {
            $recommendations[] = [
                'type' => 'good_match',
                'message' => 'You have a good foundation for this role.'
            ];
        } else {
            $recommendations[] = [
                'type' => 'needs_improvement',
                'message' => 'Consider developing some additional skills for this role.'
            ];
        }

        // Add specific skill recommendations
        $missingSkills = $skillMatch['missing_skills'] ?? [];
        if (!empty($missingSkills)) {
            $recommendations[] = [
                'type' => 'skill_gap',
                'message' => 'Consider learning: ' . implode(', ', array_slice($missingSkills, 0, 5)),
                'skills' => array_slice($missingSkills, 0, 5)
            ];
        }

        return $recommendations;
    }

    /**
     * Get comprehensive skill gap analysis for a job
     *
     * @param Request $request
     * @param int $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkillGapAnalysis(Request $request, int $jobId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $job = Job::with(['category', 'jobType'])->find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        try {
            $analysis = $this->skillGapAnalysis->analyzeSkillGap($user, $job);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze skill gap',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get career path suggestions based on user's current skills
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCareerPaths(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        try {
            $careerPaths = $this->skillGapAnalysis->getCareerPaths();

            return response()->json([
                'success' => true,
                'data' => [
                    'career_paths' => $careerPaths
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get career paths',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
