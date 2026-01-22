<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Azure ML K-Means Clustering Service
 *
 * Integrates with Azure Machine Learning for advanced K-means clustering
 * with fallback to local BasicKMeansClusteringService if Azure ML is unavailable.
 */
class AzureMLClusteringService
{
    protected $config;
    protected $accessToken;
    protected $tokenExpiry;
    protected $localClusteringService;

    public function __construct()
    {
        $this->config = config('azure-ml');
        $this->localClusteringService = new BasicKMeansClusteringService(
            $this->config['clustering']['default_k'] ?? 5,
            $this->config['clustering']['max_iterations'] ?? 100
        );
    }

    /**
     * Run K-means clustering on job data using Azure ML
     *
     * @param int|null $k Number of clusters (optional, uses config default)
     * @return array
     */
    public function runJobClustering(?int $k = null): array
    {
        $k = $k ?? $this->config['clustering']['default_k'];
        $cacheKey = $this->config['cache']['prefix'] . 'job_clusters_' . $k;

        // Check cache first
        if ($this->config['cache']['enabled'] && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $data = $this->getJobTrainingData();

            if (empty($data)) {
                return ['clusters' => [], 'centroids' => [], 'source' => 'empty'];
            }

            $result = $this->callAzureMLEndpoint($data, $k);
            $result['source'] = 'azure_ml';

            // Cache the result
            if ($this->config['cache']['enabled']) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            return $result;

        } catch (Exception $e) {
            $this->logError('Job clustering failed', $e);
            return $this->fallbackToLocalClustering('job', $k);
        }
    }

    /**
     * Run K-means clustering on user data using Azure ML
     *
     * @param int|null $k Number of clusters (optional)
     * @return array
     */
    public function runUserClustering(?int $k = null): array
    {
        $k = $k ?? $this->config['clustering']['default_k'];
        $cacheKey = $this->config['cache']['prefix'] . 'user_clusters_' . $k;

        if ($this->config['cache']['enabled'] && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $data = $this->getUserTrainingData();

            if (empty($data)) {
                return ['clusters' => [], 'centroids' => [], 'source' => 'empty'];
            }

            $result = $this->callAzureMLEndpoint($data, $k);
            $result['source'] = 'azure_ml';

            if ($this->config['cache']['enabled']) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            return $result;

        } catch (Exception $e) {
            $this->logError('User clustering failed', $e);
            return $this->fallbackToLocalClustering('user', $k);
        }
    }

    /**
     * Get job recommendations using Azure ML clustering
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getJobRecommendations(int $userId, int $limit = 5)
    {
        $user = User::with('jobSeekerProfile')->find($userId);
        if (!$user || !$user->jobSeekerProfile) {
            return collect([]);
        }

        $profile = $user->jobSeekerProfile;
        $preferredCategories = $profile->preferred_categories ?? [];

        if (empty($preferredCategories)) {
            return Job::where('status', 1)
                ->with(['category', 'jobType', 'employer.employerProfile'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        }

        try {
            // Get all active jobs
            $allJobs = Job::where('status', 1)
                ->with(['category', 'jobType', 'employer.employerProfile'])
                ->get();

            if ($allJobs->isEmpty()) {
                return collect([]);
            }

            // Prepare job features for clustering
            $jobData = [];
            $jobModels = [];
            foreach ($allJobs as $job) {
                $features = $this->extractJobFeatures($job);
                $jobData[] = $features;
                $jobModels[$job->id] = $job;
            }

            // Run clustering via Azure ML
            $clusterResult = $this->callAzureMLEndpoint($jobData, $this->config['clustering']['default_k']);

            if (empty($clusterResult['clusters']) || empty($clusterResult['labels'])) {
                throw new Exception('Invalid clustering result from Azure ML');
            }

            // Create user feature vector
            $userFeatures = $this->extractUserFeatures($user);

            // Find which cluster the user belongs to
            $userClusterIndex = $this->findUserCluster($userFeatures, $clusterResult['centroids']);

            // Get jobs from user's cluster, filtered by preferences
            $recommendedJobs = collect();
            $labels = $clusterResult['labels'];

            foreach ($allJobs as $index => $job) {
                if (isset($labels[$index]) && $labels[$index] == $userClusterIndex) {
                    if (in_array($job->category_id, $preferredCategories)) {
                        $job->cluster_score = $this->calculateClusterScore($job, $clusterResult['centroids'][$userClusterIndex]);
                        $recommendedJobs->push($job);
                    }
                }
            }

            // If not enough jobs, expand to nearby clusters
            if ($recommendedJobs->count() < $limit) {
                $additionalJobs = $this->getJobsFromNearbyClusters(
                    $userClusterIndex,
                    $clusterResult,
                    $allJobs,
                    $preferredCategories,
                    $recommendedJobs->pluck('id')->toArray(),
                    $limit - $recommendedJobs->count()
                );
                $recommendedJobs = $recommendedJobs->concat($additionalJobs);
            }

            return $recommendedJobs
                ->sortByDesc('cluster_score')
                ->take($limit);

        } catch (Exception $e) {
            $this->logError('Azure ML recommendation failed, using fallback', $e);
            return $this->localClusteringService->getJobRecommendations($userId, $limit);
        }
    }

    /**
     * Get user recommendations for a job using Azure ML
     *
     * @param int $jobId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserRecommendations(int $jobId, int $limit = 5)
    {
        try {
            $job = Job::find($jobId);
            if (!$job) {
                return collect([]);
            }

            $users = User::where('role', 'jobseeker')
                ->whereHas('jobSeekerProfile')
                ->with('jobSeekerProfile')
                ->get();

            if ($users->isEmpty()) {
                return collect([]);
            }

            // Prepare user features
            $userData = [];
            foreach ($users as $user) {
                $userData[] = $this->extractUserFeatures($user);
            }

            // Run clustering
            $clusterResult = $this->callAzureMLEndpoint($userData, $this->config['clustering']['default_k']);

            // Get job features and find matching cluster
            $jobFeatures = $this->extractJobFeatures($job);
            $jobClusterIndex = $this->findUserCluster($jobFeatures, $clusterResult['centroids']);

            // Get users from matching cluster
            $matchingUsers = collect();
            $labels = $clusterResult['labels'] ?? [];

            foreach ($users as $index => $user) {
                if (isset($labels[$index]) && $labels[$index] == $jobClusterIndex) {
                    $user->match_score = $this->calculateUserJobMatchScore($user, $job);
                    $matchingUsers->push($user);
                }
            }

            return $matchingUsers
                ->sortByDesc('match_score')
                ->take($limit);

        } catch (Exception $e) {
            $this->logError('Azure ML user recommendation failed', $e);
            return $this->localClusteringService->getUserRecommendations($jobId, $limit);
        }
    }

    /**
     * Get optimal number of clusters using Elbow method via Azure ML
     *
     * @param string $dataType 'job' or 'user'
     * @param int $maxK Maximum K to test
     * @return array
     */
    public function findOptimalK(string $dataType = 'job', int $maxK = 10): array
    {
        try {
            $data = $dataType === 'job'
                ? $this->getJobTrainingData()
                : $this->getUserTrainingData();

            if (empty($data)) {
                return ['optimal_k' => 3, 'inertias' => [], 'method' => 'default'];
            }

            $inertias = [];
            for ($k = 2; $k <= min($maxK, count($data)); $k++) {
                $result = $this->callAzureMLEndpoint($data, $k, true);
                $inertias[$k] = $result['inertia'] ?? 0;
            }

            // Find elbow point
            $optimalK = $this->findElbowPoint($inertias);

            return [
                'optimal_k' => $optimalK,
                'inertias' => $inertias,
                'method' => 'elbow'
            ];

        } catch (Exception $e) {
            $this->logError('Optimal K calculation failed', $e);
            return ['optimal_k' => 5, 'inertias' => [], 'method' => 'default'];
        }
    }

    /**
     * Get cluster analysis with silhouette scores
     *
     * @param string $dataType
     * @param int $k
     * @return array
     */
    public function getClusterAnalysis(string $dataType = 'job', int $k = null): array
    {
        $k = $k ?? $this->config['clustering']['default_k'];

        try {
            $data = $dataType === 'job'
                ? $this->getJobTrainingData()
                : $this->getUserTrainingData();

            $result = $this->callAzureMLEndpoint($data, $k, true);

            return [
                'clusters' => $result['clusters'] ?? [],
                'centroids' => $result['centroids'] ?? [],
                'inertia' => $result['inertia'] ?? 0,
                'silhouette_score' => $result['silhouette_score'] ?? 0,
                'cluster_sizes' => $this->calculateClusterSizes($result['labels'] ?? [], $k),
                'source' => 'azure_ml'
            ];

        } catch (Exception $e) {
            $this->logError('Cluster analysis failed', $e);
            return $this->fallbackToLocalClustering($dataType, $k);
        }
    }

    /**
     * Get labor market insights using Azure ML clustering
     *
     * @return array
     */
    public function getLaborMarketInsights(): array
    {
        try {
            $jobClusters = $this->runJobClustering();
            $userClusters = $this->runUserClustering();

            return [
                'job_clusters' => $this->formatClusterInsights($jobClusters, 'job'),
                'user_clusters' => $this->formatClusterInsights($userClusters, 'user'),
                'supply_demand_analysis' => $this->analyzeSupplyDemand($jobClusters, $userClusters),
                'trending_skills' => $this->identifyTrendingSkills(),
                'market_gaps' => $this->identifyMarketGaps($jobClusters, $userClusters),
                'source' => $jobClusters['source'] ?? 'azure_ml'
            ];

        } catch (Exception $e) {
            $this->logError('Labor market insights failed', $e);
            return $this->localClusteringService->getLaborMarketInsights();
        }
    }

    /**
     * Call Azure ML endpoint for clustering
     *
     * @param array $data
     * @param int $k
     * @param bool $includeMetrics
     * @return array
     */
    protected function callAzureMLEndpoint(array $data, int $k, bool $includeMetrics = false): array
    {
        $endpointUrl = $this->config['endpoint_url'];
        $endpointKey = $this->config['endpoint_key'];

        if (empty($endpointUrl) || empty($endpointKey)) {
            throw new Exception('Azure ML endpoint not configured');
        }

        // Prepare request payload
        $payload = [
            'data' => $data,
            'k' => $k,
            'max_iterations' => $this->config['clustering']['max_iterations'],
            'tolerance' => $this->config['clustering']['tolerance'],
            'algorithm' => $this->config['clustering']['algorithm'],
            'init_method' => $this->config['clustering']['init_method'],
            'scaling' => $this->config['scaling'],
            'include_metrics' => $includeMetrics,
        ];

        $response = Http::timeout($this->config['timeout']['request'])
            ->connectTimeout($this->config['timeout']['connection'])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $endpointKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($endpointUrl, $payload);

        if (!$response->successful()) {
            throw new Exception('Azure ML request failed: ' . $response->body());
        }

        $result = $response->json();

        // Validate response structure
        if (!isset($result['labels']) && !isset($result['clusters'])) {
            throw new Exception('Invalid response from Azure ML endpoint');
        }

        return $result;
    }

    /**
     * Get Azure AD access token for authentication
     *
     * @return string
     */
    protected function getAccessToken(): string
    {
        // Check if we have a valid cached token
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $cacheKey = $this->config['cache']['prefix'] . 'access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $tenantId = $this->config['tenant_id'];
        $clientId = $this->config['client_id'];
        $clientSecret = $this->config['client_secret'];

        if (empty($tenantId) || empty($clientId) || empty($clientSecret)) {
            throw new Exception('Azure AD credentials not configured');
        }

        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
            [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://ml.azure.com/.default',
            ]
        );

        if (!$response->successful()) {
            throw new Exception('Failed to obtain Azure AD token: ' . $response->body());
        }

        $tokenData = $response->json();
        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpiry = time() + ($tokenData['expires_in'] - 60);

        // Cache the token
        Cache::put($cacheKey, $this->accessToken, $tokenData['expires_in'] - 60);

        return $this->accessToken;
    }

    /**
     * Extract features from a job for clustering
     *
     * @param Job $job
     * @return array
     */
    protected function extractJobFeatures(Job $job): array
    {
        return [
            'category_id' => (float) $job->category_id,
            'job_type_id' => (float) $job->job_type_id,
            'location_hash' => (float) (crc32(strtolower($job->location ?: '')) % 1000),
            'salary_normalized' => (float) $this->normalizeSalary($job->salary_range),
            'experience_level' => (float) $this->extractExperienceLevel($job->requirements),
            'is_remote' => (float) ($job->is_remote ?? 0),
            'days_since_posted' => (float) now()->diffInDays($job->created_at),
            'skills_score' => (float) $this->calculateSkillsScore($job->requirements . ' ' . $job->description),
        ];
    }

    /**
     * Extract features from a user for clustering
     *
     * @param User $user
     * @return array
     */
    protected function extractUserFeatures(User $user): array
    {
        $profile = $user->jobSeekerProfile;

        if (!$profile) {
            return [
                'category_preference' => 0.0,
                'job_type_preference' => 0.0,
                'location_hash' => 0.0,
                'expected_salary' => 0.0,
                'experience_years' => 0.0,
                'open_to_remote' => 0.0,
                'skills_score' => 0.0,
            ];
        }

        $preferredCategories = $profile->preferred_categories ?? [];
        $preferredJobTypes = $profile->preferred_job_types ?? [];
        $preferredLocations = $profile->preferred_locations ?? [];

        return [
            'category_preference' => (float) (!empty($preferredCategories) ? $preferredCategories[0] : 0),
            'job_type_preference' => (float) (!empty($preferredJobTypes) ? $preferredJobTypes[0] : 0),
            'location_hash' => (float) (!empty($preferredLocations) ? crc32(strtolower($preferredLocations[0])) % 1000 : 0),
            'expected_salary' => (float) (($profile->expected_salary_min ?? 0) + ($profile->expected_salary_max ?? 0)) / 2,
            'experience_years' => (float) ($profile->total_experience_years ?? 0),
            'open_to_remote' => (float) ($profile->open_to_remote ?? 0),
            'skills_score' => (float) $this->calculateUserSkillsScore($profile),
        ];
    }

    /**
     * Get job training data
     *
     * @return array
     */
    protected function getJobTrainingData(): array
    {
        return Job::where('status', 1)
            ->get()
            ->map(fn($job) => $this->extractJobFeatures($job))
            ->toArray();
    }

    /**
     * Get user training data
     *
     * @return array
     */
    protected function getUserTrainingData(): array
    {
        return User::where('role', 'jobseeker')
            ->whereHas('jobSeekerProfile')
            ->with('jobSeekerProfile')
            ->get()
            ->map(fn($user) => $this->extractUserFeatures($user))
            ->toArray();
    }

    /**
     * Find which cluster a user belongs to
     *
     * @param array $userFeatures
     * @param array $centroids
     * @return int
     */
    protected function findUserCluster(array $userFeatures, array $centroids): int
    {
        $minDistance = PHP_FLOAT_MAX;
        $closestCluster = 0;

        foreach ($centroids as $index => $centroid) {
            $distance = $this->calculateEuclideanDistance($userFeatures, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestCluster = $index;
            }
        }

        return $closestCluster;
    }

    /**
     * Calculate Euclidean distance between two points
     *
     * @param array $point1
     * @param array $point2
     * @return float
     */
    protected function calculateEuclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;
        foreach ($point1 as $key => $value) {
            if (isset($point2[$key])) {
                $sum += pow($value - $point2[$key], 2);
            }
        }
        return sqrt($sum);
    }

    /**
     * Get jobs from nearby clusters
     *
     * @param int $primaryClusterIndex
     * @param array $clusterResult
     * @param \Illuminate\Support\Collection $allJobs
     * @param array $preferredCategories
     * @param array $excludeIds
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getJobsFromNearbyClusters(
        int $primaryClusterIndex,
        array $clusterResult,
        $allJobs,
        array $preferredCategories,
        array $excludeIds,
        int $limit
    ) {
        $primaryCentroid = $clusterResult['centroids'][$primaryClusterIndex];
        $nearbyJobs = collect();

        // Calculate distances to other clusters
        $clusterDistances = [];
        foreach ($clusterResult['centroids'] as $index => $centroid) {
            if ($index !== $primaryClusterIndex) {
                $clusterDistances[$index] = $this->calculateEuclideanDistance($primaryCentroid, $centroid);
            }
        }
        asort($clusterDistances);

        $labels = $clusterResult['labels'] ?? [];

        foreach ($clusterDistances as $clusterIndex => $distance) {
            if ($nearbyJobs->count() >= $limit) break;

            foreach ($allJobs as $index => $job) {
                if (isset($labels[$index]) && $labels[$index] == $clusterIndex) {
                    if (!in_array($job->id, $excludeIds) && in_array($job->category_id, $preferredCategories)) {
                        $job->cluster_score = 1 / (1 + $distance);
                        $nearbyJobs->push($job);

                        if ($nearbyJobs->count() >= $limit) break;
                    }
                }
            }
        }

        return $nearbyJobs;
    }

    /**
     * Calculate cluster score for a job
     *
     * @param Job $job
     * @param array $centroid
     * @return float
     */
    protected function calculateClusterScore(Job $job, array $centroid): float
    {
        $features = $this->extractJobFeatures($job);
        $distance = $this->calculateEuclideanDistance($features, $centroid);

        $score = 1 / (1 + $distance);

        // Boost recent jobs
        $daysSincePosted = now()->diffInDays($job->created_at);
        if ($daysSincePosted <= 7) {
            $score *= 1.3;
        } elseif ($daysSincePosted <= 30) {
            $score *= 1.1;
        }

        return $score;
    }

    /**
     * Calculate match score between user and job
     *
     * @param User $user
     * @param Job $job
     * @return float
     */
    protected function calculateUserJobMatchScore(User $user, Job $job): float
    {
        $userFeatures = $this->extractUserFeatures($user);
        $jobFeatures = $this->extractJobFeatures($job);

        $distance = $this->calculateEuclideanDistance($userFeatures, $jobFeatures);
        return 1 / (1 + $distance);
    }

    /**
     * Fallback to local clustering service
     *
     * @param string $type
     * @param int $k
     * @return array
     */
    protected function fallbackToLocalClustering(string $type, int $k): array
    {
        if (!$this->config['fallback']['enabled']) {
            return ['clusters' => [], 'centroids' => [], 'source' => 'fallback_disabled'];
        }

        $this->logInfo("Using local clustering fallback for {$type}");

        $result = $type === 'job'
            ? $this->localClusteringService->runJobClustering()
            : $this->localClusteringService->runUserClustering();

        $result['source'] = 'local_fallback';
        return $result;
    }

    /**
     * Find elbow point in inertia curve
     *
     * @param array $inertias
     * @return int
     */
    protected function findElbowPoint(array $inertias): int
    {
        if (empty($inertias)) {
            return 3;
        }

        $keys = array_keys($inertias);
        $values = array_values($inertias);
        $n = count($values);

        if ($n < 3) {
            return $keys[0];
        }

        // Calculate second derivative to find elbow
        $maxCurvature = 0;
        $elbowK = $keys[1];

        for ($i = 1; $i < $n - 1; $i++) {
            $curvature = abs($values[$i - 1] - 2 * $values[$i] + $values[$i + 1]);
            if ($curvature > $maxCurvature) {
                $maxCurvature = $curvature;
                $elbowK = $keys[$i];
            }
        }

        return $elbowK;
    }

    /**
     * Calculate cluster sizes from labels
     *
     * @param array $labels
     * @param int $k
     * @return array
     */
    protected function calculateClusterSizes(array $labels, int $k): array
    {
        $sizes = array_fill(0, $k, 0);
        foreach ($labels as $label) {
            if (isset($sizes[$label])) {
                $sizes[$label]++;
            }
        }
        return $sizes;
    }

    /**
     * Format cluster insights for reporting
     *
     * @param array $clusterResult
     * @param string $type
     * @return array
     */
    protected function formatClusterInsights(array $clusterResult, string $type): array
    {
        $insights = [];
        $centroids = $clusterResult['centroids'] ?? [];
        $labels = $clusterResult['labels'] ?? [];

        foreach ($centroids as $index => $centroid) {
            $clusterSize = count(array_filter($labels, fn($l) => $l == $index));

            $insights[] = [
                'cluster_id' => $index,
                'size' => $clusterSize,
                'centroid' => $centroid,
                'characteristics' => $this->interpretCentroid($centroid, $type),
            ];
        }

        return $insights;
    }

    /**
     * Interpret centroid characteristics
     *
     * @param array $centroid
     * @param string $type
     * @return array
     */
    protected function interpretCentroid(array $centroid, string $type): array
    {
        $characteristics = [];

        if ($type === 'job') {
            $characteristics['experience_level'] = $this->interpretExperienceLevel($centroid['experience_level'] ?? 0);
            $characteristics['salary_range'] = $this->interpretSalaryRange($centroid['salary_normalized'] ?? 0);
            $characteristics['remote_preference'] = ($centroid['is_remote'] ?? 0) > 0.5 ? 'Remote preferred' : 'On-site';
        } else {
            $characteristics['experience_years'] = round($centroid['experience_years'] ?? 0, 1) . ' years';
            $characteristics['salary_expectation'] = $this->interpretSalaryRange($centroid['expected_salary'] ?? 0);
            $characteristics['remote_preference'] = ($centroid['open_to_remote'] ?? 0) > 0.5 ? 'Open to remote' : 'Prefers on-site';
        }

        return $characteristics;
    }

    /**
     * Analyze supply and demand from clusters
     *
     * @param array $jobClusters
     * @param array $userClusters
     * @return array
     */
    protected function analyzeSupplyDemand(array $jobClusters, array $userClusters): array
    {
        $jobCount = count($jobClusters['labels'] ?? []);
        $userCount = count($userClusters['labels'] ?? []);

        return [
            'total_jobs' => $jobCount,
            'total_candidates' => $userCount,
            'ratio' => $userCount > 0 ? round($jobCount / $userCount, 2) : 0,
            'market_state' => $this->interpretMarketState($jobCount, $userCount),
        ];
    }

    /**
     * Interpret market state
     *
     * @param int $jobs
     * @param int $users
     * @return string
     */
    protected function interpretMarketState(int $jobs, int $users): string
    {
        if ($users == 0) return 'No candidates';

        $ratio = $jobs / $users;

        if ($ratio > 1.5) return 'Candidate-driven market';
        if ($ratio < 0.5) return 'Employer-driven market';
        return 'Balanced market';
    }

    /**
     * Identify trending skills from job data
     *
     * @return array
     */
    protected function identifyTrendingSkills(): array
    {
        $recentJobs = Job::where('status', 1)
            ->where('created_at', '>=', now()->subDays(30))
            ->get(['requirements', 'description']);

        $skillCounts = [];
        $skills = [
            'php', 'javascript', 'python', 'java', 'react', 'angular', 'vue',
            'laravel', 'nodejs', 'mysql', 'postgresql', 'mongodb', 'aws',
            'docker', 'kubernetes', 'git', 'agile', 'scrum', 'typescript'
        ];

        foreach ($recentJobs as $job) {
            $text = strtolower($job->requirements . ' ' . $job->description);
            foreach ($skills as $skill) {
                if (strpos($text, $skill) !== false) {
                    $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($skillCounts);
        return array_slice($skillCounts, 0, 10, true);
    }

    /**
     * Identify market gaps between job supply and candidate availability
     *
     * @param array $jobClusters
     * @param array $userClusters
     * @return array
     */
    protected function identifyMarketGaps(array $jobClusters, array $userClusters): array
    {
        // Simplified gap analysis
        $gaps = [];

        $jobCentroids = $jobClusters['centroids'] ?? [];
        $userCentroids = $userClusters['centroids'] ?? [];

        foreach ($jobCentroids as $jIndex => $jobCentroid) {
            $minDistance = PHP_FLOAT_MAX;

            foreach ($userCentroids as $userCentroid) {
                $distance = $this->calculateEuclideanDistance($jobCentroid, $userCentroid);
                $minDistance = min($minDistance, $distance);
            }

            if ($minDistance > 50) { // Threshold for significant gap
                $gaps[] = [
                    'job_cluster' => $jIndex,
                    'gap_score' => $minDistance,
                    'characteristics' => $this->interpretCentroid($jobCentroid, 'job'),
                ];
            }
        }

        return $gaps;
    }

    // Helper methods

    protected function normalizeSalary(?string $salary): float
    {
        if (empty($salary)) return 0;
        preg_match_all('/\d+/', $salary, $matches);
        $numbers = $matches[0];
        if (empty($numbers)) return 0;
        return count($numbers) >= 2 ? (($numbers[0] + $numbers[1]) / 2) : (float) $numbers[0];
    }

    protected function extractExperienceLevel(?string $requirements): float
    {
        if (empty($requirements)) return 0;
        $requirements = strtolower($requirements);

        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)\s*years?/i', $requirements, $matches)) {
            return ($matches[1] + $matches[2]) / 2;
        }
        if (preg_match('/(\d+)\s*(?:\+|or more)\s*years?/i', $requirements, $matches)) {
            return (float) $matches[1];
        }
        if (preg_match('/(\d+)\s*years?/i', $requirements, $matches)) {
            return (float) $matches[1];
        }
        if (strpos($requirements, 'senior') !== false) return 5;
        if (strpos($requirements, 'junior') !== false) return 2;
        if (strpos($requirements, 'entry') !== false) return 0;

        return 3;
    }

    protected function calculateSkillsScore(string $text): float
    {
        $text = strtolower($text);
        $skills = [
            'php' => 10, 'javascript' => 10, 'python' => 10, 'java' => 10,
            'react' => 8, 'angular' => 8, 'vue' => 8, 'laravel' => 8,
            'mysql' => 6, 'postgresql' => 6, 'mongodb' => 6,
            'aws' => 5, 'docker' => 5, 'kubernetes' => 5,
        ];

        $score = 0;
        foreach ($skills as $skill => $weight) {
            if (strpos($text, $skill) !== false) {
                $score += $weight;
            }
        }

        return $score;
    }

    protected function calculateUserSkillsScore($profile): float
    {
        $skills = $profile->skills ?? '';
        if (is_array($skills)) {
            $skills = implode(' ', $skills);
        }
        return $this->calculateSkillsScore($skills);
    }

    protected function interpretExperienceLevel(float $level): string
    {
        if ($level <= 1) return 'Entry level';
        if ($level <= 3) return 'Junior (1-3 years)';
        if ($level <= 5) return 'Mid-level (3-5 years)';
        return 'Senior (5+ years)';
    }

    protected function interpretSalaryRange(float $salary): string
    {
        if ($salary <= 20000) return 'Entry level salary';
        if ($salary <= 50000) return 'Mid-range salary';
        if ($salary <= 100000) return 'Senior salary';
        return 'Executive salary';
    }

    protected function logError(string $message, Exception $e): void
    {
        if ($this->config['logging']['enabled']) {
            Log::channel($this->config['logging']['channel'])->error($message, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function logInfo(string $message): void
    {
        if ($this->config['logging']['enabled']) {
            Log::channel($this->config['logging']['channel'])->info($message);
        }
    }

    /**
     * Clear all Azure ML related caches
     *
     * @return void
     */
    public function clearCache(): void
    {
        $patterns = [
            $this->config['cache']['prefix'] . 'job_clusters_*',
            $this->config['cache']['prefix'] . 'user_clusters_*',
            $this->config['cache']['prefix'] . 'access_token',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Check if Azure ML endpoint is configured and accessible
     *
     * @return array
     */
    public function healthCheck(): array
    {
        $status = [
            'configured' => false,
            'accessible' => false,
            'message' => '',
        ];

        if (empty($this->config['endpoint_url']) || empty($this->config['endpoint_key'])) {
            $status['message'] = 'Azure ML endpoint not configured';
            return $status;
        }

        $status['configured'] = true;

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['endpoint_key'],
                ])
                ->get($this->config['endpoint_url']);

            $status['accessible'] = $response->successful() || $response->status() === 405;
            $status['message'] = $status['accessible'] ? 'Endpoint accessible' : 'Endpoint returned error';

        } catch (Exception $e) {
            $status['message'] = 'Connection failed: ' . $e->getMessage();
        }

        return $status;
    }
}
