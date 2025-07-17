<?php

namespace App\Services;

use App\Models\SavedJob;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;

class KMeansClusteringService
{
    protected $k;
    protected $maxIterations;

    public function __construct($k = 3, $maxIterations = 20)
    {
        $this->k = $k; // Number of clusters
        $this->maxIterations = $maxIterations;
    }

    /**
     * Run K-means clustering on job data
     *
     * @return array
     */
    public function runJobClustering()
    {
        $data = $this->getJobTrainingData();
        return $this->runKMeans($data);
    }

    /**
     * Run K-means clustering on user data
     *
     * @return array
     */
    public function runUserClustering()
    {
        $data = $this->getUserTrainingData();
        return $this->runKMeans($data);
    }

    /**
     * Run K-means algorithm on provided data
     *
     * @param array $data
     * @return array
     */
    protected function runKMeans($data)
    {
        if (empty($data)) {
            return [];
        }

        // Initialize centroids randomly
        $centroids = collect($data)->random(min($this->k, count($data)))->values()->all();
        $prevCentroids = [];
        $clusters = [];

        $iterations = $this->maxIterations;
        $converged = false;

        while ($iterations-- && !$converged) {
            $clusters = array_fill(0, $this->k, []);

            // Assign points to clusters
            foreach ($data as $index => $point) {
                $distances = array_map(fn($centroid) => $this->calculateDistance($point, $centroid), $centroids);
                $closest = array_keys($distances, min($distances))[0];
                $clusters[$closest][] = [
                    'point' => $point,
                    'index' => $index
                ];
            }

            // Store previous centroids to check convergence
            $prevCentroids = $centroids;

            // Recalculate centroids
            foreach ($clusters as $i => $cluster) {
                if (!empty($cluster)) {
                    $points = array_column($cluster, 'point');
                    $centroids[$i] = $this->calculateMean($points);
                }
            }

            // Check convergence
            $converged = $this->hasConverged($prevCentroids, $centroids);
        }

        return [
            'clusters' => $clusters,
            'centroids' => $centroids
        ];
    }

    /**
     * Get training data from jobs
     *
     * @return array
     */
    protected function getJobTrainingData()
    {
        return Job::where('status', 1)->get()->map(function ($job) {
            return $this->getJobFeatures($job);
        })->toArray();
    }

    /**
     * Get training data from users
     *
     * @return array
     */
    protected function getUserTrainingData()
    {
        return User::where('role', 'user')->get()->map(function ($user) {
            $preferredCategories = $user->preferred_categories ? json_decode($user->preferred_categories, true) : [];
            $preferredJobTypes = $user->preferred_job_types ? json_decode($user->preferred_job_types, true) : [];

            // Get first preferred category and job type if available
            $categoryId = !empty($preferredCategories) ? (int)$preferredCategories[0] : 0;
            $jobTypeId = !empty($preferredJobTypes) ? (int)$preferredJobTypes[0] : 0;

            return [
                'id' => $user->id,
                'category_id' => $categoryId,
                'job_type_id' => $jobTypeId,
                'location' => $user->preferred_location ? crc32(strtolower($user->preferred_location)) : 0,
                'experience' => (int) $user->experience_years ?? 0,
                'salary' => $this->normalizeSalary($user->preferred_salary_range),
            ];
        })->toArray();
    }

    /**
     * Get job features
     *
     * @param Job $job
     * @return array
     */
    protected function getJobFeatures(Job $job): array
    {
        return [
            'job_type_id' => (int) $job->job_type_id,
            'location_hash' => crc32($job->location),
            'salary_range_normalized' => $this->normalizeSalaryRange($job->salary_range),
            'experience_level' => $this->extractExperienceLevel($job->requirements),
            'skills_hash' => $this->calculateSkillsHash($job->requirements . ' ' . $job->description),
        ];
    }

    /**
     * Calculate distance between two points
     *
     * @param array $point1
     * @param array $point2
     * @return float
     */
    protected function calculateDistance(array $point1, array $point2): float
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
     * Calculate Euclidean distance between two points
     *
     * @param array $point1
     * @param array $point2
     * @return float
     */
    protected function euclideanDistance($point1, $point2)
    {
        $sum = 0;
        foreach ($point1 as $key => $value) {
            if ($key !== 'id' && $key !== 'user_id') {
                $sum += pow(($value - $point2[$key]), 2);
            }
        }
        return sqrt($sum);
    }

    /**
     * Calculate mean of points
     *
     * @param array $points
     * @return array
     */
    protected function calculateMean($points)
    {
        $mean = [];
        $count = count($points);

        if ($count === 0) {
            return array_fill_keys(array_keys($points[0] ?? []), 0);
        }

        foreach ($points[0] as $key => $value) {
            if ($key !== 'id' && $key !== 'user_id') {
                $mean[$key] = array_sum(array_column($points, $key)) / $count;
            }
        }

        return $mean;
    }

    /**
     * Check if centroids have converged
     *
     * @param array $prevCentroids
     * @param array $centroids
     * @return bool
     */
    protected function hasConverged($prevCentroids, $centroids)
    {
        if (empty($prevCentroids)) {
            return false;
        }

        $threshold = 0.001;
        foreach ($centroids as $i => $centroid) {
            $distance = $this->euclideanDistance($prevCentroids[$i], $centroid);
            if ($distance > $threshold) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize salary string to numeric value
     *
     * @param string|null $salary
     * @return int
     */
    protected function normalizeSalary($salary)
    {
        if (empty($salary)) {
            return 0;
        }

        // Extract numbers from salary string
        preg_match_all('/\d+/', $salary, $matches);
        $numbers = $matches[0];

        if (empty($numbers)) {
            return 0;
        }

        // If there are two numbers (range), take average
        if (count($numbers) >= 2) {
            return (int)(($numbers[0] + $numbers[1]) / 2);
        }

        return (int)$numbers[0];
    }

    /**
     * Normalize salary range string to numeric value
     *
     * @param string|null $salaryRange
     * @return int
     */
    protected function normalizeSalaryRange($salaryRange)
    {
        if (empty($salaryRange)) {
            return 0;
        }

        // Extract numbers from salary range string
        preg_match_all('/\d+/', $salaryRange, $matches);
        $numbers = $matches[0];

        if (empty($numbers)) {
            return 0;
        }

        // If there are two numbers (range), take average
        if (count($numbers) >= 2) {
            return (int)(($numbers[0] + $numbers[1]) / 2);
        }

        return (int)$numbers[0];
    }

    /**
     * Extract experience level from job requirements
     *
     * @param string $requirements
     * @return string
     */
    protected function extractExperienceLevel($requirements)
    {
        // Implement your logic to extract experience level from job requirements
        // This is a placeholder and should be replaced with the actual implementation
        return 'Unknown';
    }

    /**
     * Calculate skills hash from job requirements and description
     *
     * @param string $requirements
     * @return string
     */
    protected function calculateSkillsHash($requirements)
    {
        // Implement your logic to calculate skills hash from job requirements and description
        // This is a placeholder and should be replaced with the actual implementation
        return '';
    }

    /**
     * Get job recommendations for a user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJobRecommendations($userId, $limit = 5)
    {
        $user = User::find($userId);
        if (!$user) {
            return collect([]);
        }

        // Get user profile data
        $userProfile = [
            'id' => $user->id,
            'category_id' => $user->preferred_categories ? (int)json_decode($user->preferred_categories)[0] : 0,
            'job_type_id' => $user->preferred_job_types ? (int)json_decode($user->preferred_job_types)[0] : 0,
            'location' => $user->preferred_location ? crc32(strtolower($user->preferred_location)) : 0,
            'experience' => (int) $user->experience_years ?? 0,
            'salary' => $this->normalizeSalary($user->preferred_salary_range),
        ];

        // Get all active jobs
        $jobs = Job::where('status', 1)->get();
        $jobProfiles = [];

        foreach ($jobs as $job) {
            $jobProfiles[$job->id] = $this->getJobFeatures($job);
        }

        // Calculate similarity scores
        $scores = [];
        foreach ($jobProfiles as $jobId => $jobProfile) {
            $scores[$jobId] = $this->calculateSimilarityScore($userProfile, $jobProfile);
        }

        // Sort by similarity (higher is better)
        arsort($scores);

        // Get top N job IDs
        $recommendedJobIds = array_slice(array_keys($scores), 0, $limit);

        // Return job objects
        return Job::whereIn('id', $recommendedJobIds)
            ->with(['category', 'jobType'])
            ->get();
    }

    /**
     * Calculate similarity score between user and job
     *
     * @param array $userProfile
     * @param array $jobProfile
     * @return float
     */
    protected function calculateSimilarityScore($userProfile, $jobProfile)
    {
        // Calculate Euclidean distance
        $distance = $this->euclideanDistance($userProfile, $jobProfile);

        // Convert distance to similarity (smaller distance = higher similarity)
        $similarity = 1 / (1 + $distance);

        // Apply additional weights
        if ($userProfile['category_id'] > 0 && $userProfile['category_id'] == $jobProfile['category_id']) {
            $similarity *= 1.5; // Boost for matching category
        }

        if ($userProfile['job_type_id'] > 0 && $userProfile['job_type_id'] == $jobProfile['job_type_id']) {
            $similarity *= 1.3; // Boost for matching job type
        }

        return $similarity;
    }

    /**
     * Get user recommendations for a job
     *
     * @param int $jobId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRecommendations($jobId, $limit = 5)
    {
        $job = Job::find($jobId);
        if (!$job) {
            return collect([]);
        }

        // Get job profile data
        $jobProfile = $this->getJobFeatures($job);

        // Get all users
        $users = User::where('role', 'user')->get();
        $userProfiles = [];

        foreach ($users as $user) {
            $userProfiles[$user->id] = [
                'id' => $user->id,
                'category_id' => $user->preferred_categories ? (int)json_decode($user->preferred_categories)[0] : 0,
                'job_type_id' => $user->preferred_job_types ? (int)json_decode($user->preferred_job_types)[0] : 0,
                'location' => $user->preferred_location ? crc32(strtolower($user->preferred_location)) : 0,
                'experience' => (int) $user->experience_years ?? 0,
                'salary' => $this->normalizeSalary($user->preferred_salary_range),
            ];
        }

        // Calculate similarity scores
        $scores = [];
        foreach ($userProfiles as $userId => $userProfile) {
            $scores[$userId] = $this->calculateSimilarityScore($userProfile, $jobProfile);
        }

        // Sort by similarity (higher is better)
        arsort($scores);

        // Get top N user IDs
        $recommendedUserIds = array_slice(array_keys($scores), 0, $limit);

        // Return user objects
        return User::whereIn('id', $recommendedUserIds)->get();
    }

    /**
     * Get labor market insights
     *
     * @return array
     */
    public function getLaborMarketInsights()
    {
        $insights = [
            'job_categories' => $this->getJobCategoriesDistribution(),
            'job_types' => $this->getJobTypesDistribution(),
            'locations' => $this->getJobLocationsDistribution(),
            'experience_levels' => $this->getExperienceLevelsDistribution(),
            'application_trends' => $this->getApplicationTrends(),
            'user_clusters' => $this->getUserClusters(),
            'job_clusters' => $this->getJobClusters(),
        ];

        return $insights;
    }

    /**
     * Get job categories distribution
     *
     * @return array
     */
    protected function getJobCategoriesDistribution()
    {
        return DB::table('jobs')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->where('jobs.status', 1)
            ->groupBy('categories.name')
            ->get()
            ->toArray();
    }

    /**
     * Get job types distribution
     *
     * @return array
     */
    protected function getJobTypesDistribution()
    {
        return DB::table('jobs')
            ->join('job_types', 'jobs.job_type_id', '=', 'job_types.id')
            ->select('job_types.name', DB::raw('count(*) as total'))
            ->where('jobs.status', 1)
            ->groupBy('job_types.name')
            ->get()
            ->toArray();
    }

    /**
     * Get job locations distribution
     *
     * @return array
     */
    protected function getJobLocationsDistribution()
    {
        return DB::table('jobs')
            ->select('location', DB::raw('count(*) as total'))
            ->where('status', 1)
            ->groupBy('location')
            ->get()
            ->toArray();
    }

    /**
     * Get experience levels distribution
     *
     * @return array
     */
    protected function getExperienceLevelsDistribution()
    {
        return DB::table('jobs')
            ->select('experience', DB::raw('count(*) as total'))
            ->where('status', 1)
            ->groupBy('experience')
            ->get()
            ->toArray();
    }

    /**
     * Get application trends
     *
     * @return array
     */
    protected function getApplicationTrends()
    {
        return DB::table('job_applications')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get()
            ->toArray();
    }

    /**
     * Get user clusters
     *
     * @return array
     */
    protected function getUserClusters()
    {
        $result = $this->runUserClustering();
        $clusters = [];

        foreach ($result['clusters'] as $i => $cluster) {
            $clusters[] = [
                'cluster_id' => $i,
                'size' => count($cluster),
                'centroid' => $result['centroids'][$i] ?? [],
            ];
        }

        return $clusters;
    }

    /**
     * Get job clusters
     *
     * @return array
     */
    protected function getJobClusters()
    {
        $result = $this->runJobClustering();
        $clusters = [];

        foreach ($result['clusters'] as $i => $cluster) {
            $clusters[] = [
                'cluster_id' => $i,
                'size' => count($cluster),
                'centroid' => $result['centroids'][$i] ?? [],
            ];
        }

        return $clusters;
    }
} 