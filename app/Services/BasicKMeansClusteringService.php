<?php

namespace App\Services;

use App\Models\SavedJob;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;

class BasicKMeansClusteringService
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
        return User::where('role', 'jobseeker')
            ->with('jobSeekerProfile')
            ->get()
            ->map(function ($user) {
                $profile = $user->jobSeekerProfile;

                // Get preferences from jobseeker profile
                $preferredCategories = $profile->preferred_categories ?? [];
                $preferredJobTypes = $profile->preferred_job_types ?? [];
                $preferredLocations = $profile->preferred_locations ?? [];

                // Get first preferred category and job type if available
                $categoryId = !empty($preferredCategories) ? (int)$preferredCategories[0] : 0;
                $jobTypeId = !empty($preferredJobTypes) ? (int)$preferredJobTypes[0] : 0;
                $location = !empty($preferredLocations) ? $preferredLocations[0] : '';

                return [
                    'id' => $user->id,
                    'category_id' => $categoryId,
                    'job_type_id' => $jobTypeId,
                    'location' => $location ? crc32(strtolower($location)) : 0,
                    'experience' => (int) ($profile->total_experience_years ?? 0),
                    'salary' => ($profile->expected_salary_min ?? 0) + ($profile->expected_salary_max ?? 0) / 2,
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
            'location_hash' => crc32(strtolower($job->location ?: '')),
            'salary_range_normalized' => $this->normalizeSalaryRange($job->salary_range),
            'experience_level' => $this->extractExperienceLevel($job->requirements),
            'skills_hash' => $this->calculateSkillsHash($job->requirements . ' ' . $job->description),
            'is_remote' => (int) ($job->is_remote ?? false),
            'urgency_score' => $this->calculateUrgencyScore($job),
            'company_size_score' => $this->calculateCompanySizeScore($job),
        ];
    }

    /**
     * Calculate urgency score based on deadline and posting date
     */
    protected function calculateUrgencyScore($job)
    {
        $now = now();
        $posted = $job->created_at;
        
        // Base score
        $score = 1;
        
        // Boost recent jobs
        $daysOld = $now->diffInDays($posted);
        if ($daysOld <= 3) $score += 3;
        elseif ($daysOld <= 7) $score += 2;
        elseif ($daysOld <= 14) $score += 1;
        
        // Factor in deadline urgency
        if ($job->deadline) {
            $daysToDeadline = $now->diffInDays($job->deadline, false);
            if ($daysToDeadline <= 7 && $daysToDeadline > 0) $score += 2;
            elseif ($daysToDeadline <= 14 && $daysToDeadline > 0) $score += 1;
        }
        
        return $score;
    }

    /**
     * Calculate company size score from employer profile
     */
    protected function calculateCompanySizeScore($job)
    {
        // Default score for unknown company size
        $score = 3;
        
        if ($job->employer && $job->employer->employerProfile) {
            $profile = $job->employer->employerProfile;
            $companyInfo = strtolower($profile->company_description ?? '');
            
            // Simple heuristics for company size
            if (strpos($companyInfo, 'startup') !== false) $score = 2;
            elseif (strpos($companyInfo, 'small') !== false) $score = 3;
            elseif (strpos($companyInfo, 'medium') !== false) $score = 4;
            elseif (strpos($companyInfo, 'large') !== false || 
                    strpos($companyInfo, 'corporation') !== false) $score = 5;
        }
        
        return $score;
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
            if ($key !== 'id' && $key !== 'user_id' && isset($point2[$key])) {
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
     * @return int
     */
    protected function extractExperienceLevel($requirements)
    {
        if (empty($requirements)) {
            return 0;
        }
        
        $requirements = strtolower($requirements);
        
        // Look for experience patterns
        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)\s*years?/i', $requirements, $matches)) {
            return (int)(($matches[1] + $matches[2]) / 2);
        }
        
        if (preg_match('/(\d+)\s*(?:\+|or more)\s*years?/i', $requirements, $matches)) {
            return (int)$matches[1];
        }
        
        if (preg_match('/(\d+)\s*years?/i', $requirements, $matches)) {
            return (int)$matches[1];
        }
        
        // Experience level keywords
        if (strpos($requirements, 'senior') !== false || strpos($requirements, 'lead') !== false) {
            return 5;
        }
        
        if (strpos($requirements, 'junior') !== false) {
            return 2;
        }
        
        if (strpos($requirements, 'entry') !== false || strpos($requirements, 'fresh') !== false) {
            return 0;
        }
        
        return 3; // Default mid-level
    }

    /**
     * Calculate skills hash from job requirements and description
     *
     * @param string $requirements
     * @return int
     */
    protected function calculateSkillsHash($requirements)
    {
        if (empty($requirements)) {
            return 0;
        }
        
        // Convert text to a numeric hash for clustering
        $cleanText = strtolower(preg_replace('/[^a-z0-9\s]/', '', $requirements));
        $words = array_filter(explode(' ', $cleanText));
        
        // Common tech skills with weights
        $techSkills = [
            'php' => 10, 'javascript' => 10, 'python' => 10, 'java' => 10,
            'react' => 8, 'angular' => 8, 'vue' => 8, 'laravel' => 8,
            'mysql' => 6, 'postgresql' => 6, 'mongodb' => 6,
            'html' => 4, 'css' => 4, 'bootstrap' => 4,
            'git' => 3, 'docker' => 3, 'aws' => 3
        ];
        
        $skillScore = 0;
        foreach ($words as $word) {
            if (isset($techSkills[$word])) {
                $skillScore += $techSkills[$word];
            }
        }
        
        // If no tech skills found, use a simple hash
        if ($skillScore === 0) {
            $skillScore = abs(crc32($cleanText)) % 100;
        }
        
        return $skillScore;
    }

    /**
     * Get job recommendations for a user using REAL K-means clustering
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJobRecommendations($userId, $limit = 5)
    {
        $user = User::with('jobSeekerProfile')->find($userId);
        if (!$user || !$user->jobSeekerProfile) {
            return collect([]);
        }

        $profile = $user->jobSeekerProfile;

        // Get user's preferred categories from jobseeker profile
        $preferredCategories = $profile->preferred_categories ?? [];

        if (empty($preferredCategories)) {
            // If no preferences, return recent jobs from all categories
            return Job::where('status', 1)
                ->with(['category', 'jobType', 'employer.employerProfile'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        }

        // ===== REAL K-MEANS CLUSTERING IMPLEMENTATION =====

        // Step 1: Get all available jobs
        $allJobs = Job::where('status', 1)
            ->with(['category', 'jobType', 'employer.employerProfile'])
            ->get();

        if ($allJobs->isEmpty()) {
            return collect([]);
        }

        // Step 2: Extract features from all jobs for clustering
        $jobData = [];
        $jobModels = [];
        foreach ($allJobs as $job) {
            $features = $this->getJobFeatures($job);
            $jobData[] = $features;
            $jobModels[$job->id] = $job;
        }

        // Step 3: Run K-means clustering on jobs
        try {
            $clusteringResult = $this->runKMeans($jobData);

            if (empty($clusteringResult['clusters'])) {
                // Fallback to similarity-based ranking if clustering fails
                return $this->rankJobsBySimilarity($allJobs, $user)->take($limit);
            }

            // Step 4: Create user profile feature vector
            $preferredJobTypes = $profile->preferred_job_types ?? [];
            $preferredLocations = $profile->preferred_locations ?? [];

            $userFeatures = [
                'job_type_id' => !empty($preferredJobTypes) ? (int)$preferredJobTypes[0] : 0,
                'location_hash' => !empty($preferredLocations) ? crc32(strtolower($preferredLocations[0])) : 0,
                'salary_range_normalized' => (($profile->expected_salary_min ?? 0) + ($profile->expected_salary_max ?? 0)) / 2,
                'experience_level' => (int)($profile->total_experience_years ?? 0),
                'skills_hash' => 0, // User doesn't have this, will use category matching
                'is_remote' => (int)($profile->open_to_remote ?? 0),
                'urgency_score' => 3, // Neutral
                'company_size_score' => 3, // Neutral
            ];

            // Step 5: Find which cluster the user is closest to
            $userClusterIndex = $this->findClosestCluster($userFeatures, $clusteringResult['centroids']);

            // Step 6: Get jobs from the user's cluster
            $userClusterJobs = collect();
            if (isset($clusteringResult['clusters'][$userClusterIndex])) {
                foreach ($clusteringResult['clusters'][$userClusterIndex] as $clusterPoint) {
                    $jobIndex = $clusterPoint['index'];
                    $jobId = array_keys($jobModels)[$jobIndex];
                    if (isset($jobModels[$jobId])) {
                        $job = $jobModels[$jobId];
                        // Add cluster distance for ranking
                        $job->cluster_distance = $this->calculateDistance(
                            $userFeatures,
                            $clusteringResult['centroids'][$userClusterIndex]
                        );
                        $userClusterJobs->push($job);
                    }
                }
            }

            // Step 7: Filter by preferred categories and rank by similarity
            $recommendedJobs = $userClusterJobs->filter(function($job) use ($preferredCategories) {
                return in_array($job->category_id, $preferredCategories);
            });

            // Step 8: Rank jobs within cluster by similarity to user profile
            $rankedJobs = $recommendedJobs->map(function($job) use ($userFeatures) {
                $jobFeatures = $this->getJobFeatures($job);
                $distance = $this->calculateDistance($userFeatures, $jobFeatures);
                $job->similarity_score = 1 / (1 + $distance); // Convert distance to similarity

                // Boost recent jobs
                $daysSincePosted = now()->diffInDays($job->created_at);
                if ($daysSincePosted <= 7) {
                    $job->similarity_score *= 1.2;
                } elseif ($daysSincePosted <= 30) {
                    $job->similarity_score *= 1.1;
                }

                return $job;
            })->sortByDesc('similarity_score');

            // Step 9: If not enough jobs in the primary cluster, get from nearby clusters
            if ($rankedJobs->count() < $limit) {
                $nearbyJobs = $this->getJobsFromNearbyClusters(
                    $userClusterIndex,
                    $clusteringResult,
                    $jobModels,
                    $preferredCategories,
                    $rankedJobs->pluck('id')->toArray(),
                    $limit - $rankedJobs->count()
                );
                $rankedJobs = $rankedJobs->concat($nearbyJobs);
            }

            return $rankedJobs->take($limit);

        } catch (\Exception $e) {
            \Log::error('K-means clustering failed: ' . $e->getMessage());

            // Fallback to similarity-based ranking
            return $this->rankJobsBySimilarity($allJobs, $user)
                ->filter(function($job) use ($preferredCategories) {
                    return in_array($job->category_id, $preferredCategories);
                })
                ->take($limit);
        }
    }

    /**
     * Find the closest cluster for a given feature vector
     *
     * @param array $features
     * @param array $centroids
     * @return int
     */
    protected function findClosestCluster($features, $centroids)
    {
        $minDistance = PHP_FLOAT_MAX;
        $closestCluster = 0;

        foreach ($centroids as $index => $centroid) {
            $distance = $this->calculateDistance($features, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestCluster = $index;
            }
        }

        return $closestCluster;
    }

    /**
     * Get jobs from nearby clusters when primary cluster doesn't have enough jobs
     *
     * @param int $primaryClusterIndex
     * @param array $clusteringResult
     * @param array $jobModels
     * @param array $preferredCategories
     * @param array $excludeIds
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getJobsFromNearbyClusters($primaryClusterIndex, $clusteringResult, $jobModels, $preferredCategories, $excludeIds, $limit)
    {
        $primaryCentroid = $clusteringResult['centroids'][$primaryClusterIndex];
        $nearbyJobs = collect();

        // Calculate distances from primary cluster to all other clusters
        $clusterDistances = [];
        foreach ($clusteringResult['centroids'] as $index => $centroid) {
            if ($index !== $primaryClusterIndex) {
                $clusterDistances[$index] = $this->calculateDistance($primaryCentroid, $centroid);
            }
        }

        // Sort clusters by distance (nearest first)
        asort($clusterDistances);

        // Get jobs from nearby clusters
        foreach ($clusterDistances as $clusterIndex => $distance) {
            if ($nearbyJobs->count() >= $limit) {
                break;
            }

            if (isset($clusteringResult['clusters'][$clusterIndex])) {
                foreach ($clusteringResult['clusters'][$clusterIndex] as $clusterPoint) {
                    $jobIndex = $clusterPoint['index'];
                    $jobId = array_keys($jobModels)[$jobIndex];

                    if (isset($jobModels[$jobId]) && !in_array($jobId, $excludeIds)) {
                        $job = $jobModels[$jobId];

                        // Only include jobs in preferred categories
                        if (in_array($job->category_id, $preferredCategories)) {
                            $job->similarity_score = 1 / (1 + $distance);
                            $nearbyJobs->push($job);

                            if ($nearbyJobs->count() >= $limit) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $nearbyJobs->sortByDesc('similarity_score');
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
        // Create normalized profiles for comparison
        $userNormalized = [
            'category_id' => $userProfile['category_id'] ?? 0,
            'job_type_id' => $userProfile['job_type_id'] ?? 0,
            'location' => $userProfile['location'] ?? 0,
            'experience' => $userProfile['experience'] ?? 0,
            'salary' => $userProfile['salary'] ?? 0,
        ];
        
        $jobNormalized = [
            'category_id' => 0, // Jobs don't have category_id in features
            'job_type_id' => $jobProfile['job_type_id'] ?? 0,
            'location' => $jobProfile['location_hash'] ?? 0,
            'experience' => $jobProfile['experience_level'] ?? 0,
            'salary' => $jobProfile['salary_range_normalized'] ?? 0,
        ];
        
        // Calculate Euclidean distance
        $distance = $this->euclideanDistance($userNormalized, $jobNormalized);

        // Convert distance to similarity (smaller distance = higher similarity)
        $similarity = 1 / (1 + $distance);

        // Apply additional weights based on job type match
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

        // Get all users with their jobseeker profiles
        $users = User::where('role', 'jobseeker')->with('jobSeekerProfile')->get();
        $userProfiles = [];

        foreach ($users as $user) {
            if (!$user->jobSeekerProfile) {
                continue;
            }

            $profile = $user->jobSeekerProfile;
            $preferredCategories = $profile->preferred_categories ?? [];
            $preferredJobTypes = $profile->preferred_job_types ?? [];
            $preferredLocations = $profile->preferred_locations ?? [];

            $userProfiles[$user->id] = [
                'id' => $user->id,
                'category_id' => !empty($preferredCategories) ? (int)$preferredCategories[0] : 0,
                'job_type_id' => !empty($preferredJobTypes) ? (int)$preferredJobTypes[0] : 0,
                'location' => !empty($preferredLocations) ? crc32(strtolower($preferredLocations[0])) : 0,
                'experience' => (int) ($profile->total_experience_years ?? 0),
                'salary' => (($profile->expected_salary_min ?? 0) + ($profile->expected_salary_max ?? 0)) / 2,
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
        // Since there's no experience column in jobs table, 
        // we'll create experience level distribution based on requirements text
        $jobs = DB::table('jobs')
            ->select('requirements')
            ->where('status', 1)
            ->whereNotNull('requirements')
            ->get();
        
        $experienceLevels = [
            'Entry Level' => 0,
            'Junior (1-2 years)' => 0,
            'Mid Level (3-5 years)' => 0,
            'Senior (5+ years)' => 0
        ];
        
        foreach ($jobs as $job) {
            $requirements = strtolower($job->requirements);
            
            if (strpos($requirements, 'entry level') !== false || 
                strpos($requirements, 'fresh grad') !== false ||
                strpos($requirements, '0 year') !== false) {
                $experienceLevels['Entry Level']++;
            } elseif (preg_match('/[12]\s*year/', $requirements)) {
                $experienceLevels['Junior (1-2 years)']++;
            } elseif (preg_match('/[345]\s*year/', $requirements)) {
                $experienceLevels['Mid Level (3-5 years)']++;
            } elseif (strpos($requirements, 'senior') !== false ||
                     strpos($requirements, '5+') !== false ||
                     preg_match('/[6789]\s*year/', $requirements)) {
                $experienceLevels['Senior (5+ years)']++;
            } else {
                $experienceLevels['Mid Level (3-5 years)']++; // Default
            }
        }
        
        // Convert to object format to match expected output
        $result = [];
        foreach ($experienceLevels as $level => $count) {
            $result[] = (object)['name' => $level, 'total' => $count];
        }
        
        return $result;
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

    /**
     * Cluster jobs by category with simple ranking
     *
     * @param \Illuminate\Database\Eloquent\Collection $categoryJobs
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function clusterJobsByCategory($categoryJobs, $user, $limit)
    {
        $profile = $user->jobSeekerProfile;

        // Get user's preferred job types from jobseeker profile
        $preferredJobTypes = $profile->preferred_job_types ?? [];

        // Simple scoring system for jobs in the same category
        $scoredJobs = $categoryJobs->map(function($job) use ($preferredJobTypes) {
            $score = 1; // Base score

            // Boost score for preferred job types (comparing IDs now)
            if (!empty($preferredJobTypes) && in_array($job->job_type_id, $preferredJobTypes)) {
                $score += 2;
            }
            
            // Boost for recent jobs
            $daysSincePosted = now()->diffInDays($job->created_at);
            if ($daysSincePosted <= 7) {
                $score += 1;
            } elseif ($daysSincePosted <= 30) {
                $score += 0.5;
            }
            
            // Add some randomness to prevent always showing the same jobs
            $score += rand(0, 50) / 100; // Add 0-0.5 random points
            
            $job->recommendation_score = $score;
            return $job;
        });
        
        // Sort by score (highest first) and return top N
        return $scoredJobs->sortByDesc('recommendation_score')
                         ->take($limit);
    }

    /**
     * Rank jobs by similarity to user profile using clustering principles
     *
     * @param \Illuminate\Database\Eloquent\Collection $jobs
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function rankJobsBySimilarity($jobs, $user)
    {
        $profile = $user->jobSeekerProfile;

        // Get user profile features from jobseeker profile
        $preferredCategories = $profile->preferred_categories ?? [];
        $preferredJobTypes = $profile->preferred_job_types ?? [];
        $preferredLocations = $profile->preferred_locations ?? [];

        $userProfile = [
            'category_id' => !empty($preferredCategories) ? (int)$preferredCategories[0] : 0,
            'job_type_id' => !empty($preferredJobTypes) ? (int)$preferredJobTypes[0] : 0,
            'location' => !empty($preferredLocations) ? crc32(strtolower($preferredLocations[0])) : 0,
            'experience' => (int) ($profile->total_experience_years ?? 0),
            'salary' => (($profile->expected_salary_min ?? 0) + ($profile->expected_salary_max ?? 0)) / 2,
        ];
        
        // Calculate similarity scores for each job
        $rankedJobs = $jobs->map(function($job) use ($userProfile, $preferredCategories) {
            $jobProfile = $this->getJobFeatures($job);
            $similarity = $this->calculateSimilarityScore($userProfile, $jobProfile);
            
            // Additional boost for category preference (even if not exact match)
            if (!empty($preferredCategories) && in_array($job->category_id, $preferredCategories)) {
                $similarity *= 1.5; // Strong boost for preferred categories
            }
            
            // Boost for recent jobs
            $daysSincePosted = now()->diffInDays($job->created_at);
            if ($daysSincePosted <= 7) {
                $similarity *= 1.2;
            } elseif ($daysSincePosted <= 30) {
                $similarity *= 1.1;
            }
            
            $job->similarity_score = $similarity;
            return $job;
        });
        
        // Sort by similarity score (highest first)
        return $rankedJobs->sortByDesc('similarity_score');
    }
}
