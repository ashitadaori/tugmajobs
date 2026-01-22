<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use App\Models\JobApplication;
use App\Models\SavedJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Enhanced K-Means Clustering Service
 *
 * Improvements over basic K-means:
 * 1. Content-based category inference (not just employer's selection)
 * 2. Weighted feature distance calculation
 * 3. Dual matching (category + content)
 * 4. Skill vector similarity using cosine distance
 * 5. Adaptive K selection
 * 6. Cluster quality metrics
 *
 * This service solves the problem of miscategorized jobs by analyzing
 * actual job content and matching based on what the job REALLY is.
 */
class EnhancedKMeansClusteringService
{
    protected ContentAnalysisService $contentAnalysis;

    protected int $k;
    protected int $maxIterations;
    protected float $convergenceThreshold;
    protected int $cacheTimeout;

    /**
     * Feature weights for distance calculation
     * Higher weight = more important in clustering
     */
    protected array $featureWeights = [
        // Content-based features (NEW - most important)
        'content_category_score' => 4.0,      // Inferred category from content
        'role_type_score' => 3.5,             // Role type indicator

        // Traditional features
        'category_match' => 3.0,              // Employer's selected category
        'skill_similarity' => 2.5,            // Skills vector similarity
        'experience_match' => 2.0,            // Experience level
        'salary_match' => 1.5,                // Salary range
        'location_match' => 1.5,              // Location
        'job_type_match' => 1.3,              // Full-time/Part-time

        // Secondary features
        'job_freshness' => 0.8,               // How recently posted
        'is_remote' => 0.5,                   // Remote work option
    ];

    /**
     * Role type indicators for better classification
     */
    protected array $roleTypes = [
        'technical' => [
            'keywords' => ['developer', 'engineer', 'programmer', 'analyst', 'architect', 'devops', 'admin'],
            'skills' => ['php', 'javascript', 'python', 'java', 'sql', 'aws', 'docker', 'linux']
        ],
        'administrative' => [
            'keywords' => ['clerk', 'secretary', 'assistant', 'receptionist', 'encoder', 'staff'],
            'skills' => ['filing', 'data entry', 'typing', 'ms office', 'excel', 'scheduling']
        ],
        'customer_facing' => [
            'keywords' => ['customer', 'sales', 'support', 'representative', 'agent', 'service'],
            'skills' => ['communication', 'phone', 'crm', 'negotiation', 'presentation']
        ],
        'creative' => [
            'keywords' => ['designer', 'artist', 'creative', 'editor', 'photographer'],
            'skills' => ['photoshop', 'illustrator', 'figma', 'video editing', 'animation']
        ],
        'management' => [
            'keywords' => ['manager', 'supervisor', 'lead', 'head', 'director', 'chief'],
            'skills' => ['leadership', 'team management', 'strategic', 'budget', 'planning']
        ],
        'manual_labor' => [
            'keywords' => ['worker', 'operator', 'driver', 'helper', 'laborer', 'technician'],
            'skills' => ['physical', 'equipment', 'maintenance', 'repair', 'assembly']
        ]
    ];

    public function __construct(int $k = 5, int $maxIterations = 100, float $convergenceThreshold = 0.0001)
    {
        $this->k = $k;
        $this->maxIterations = $maxIterations;
        $this->convergenceThreshold = $convergenceThreshold;
        $this->cacheTimeout = 7200; // 2 hours
        $this->contentAnalysis = new ContentAnalysisService();
    }

    /**
     * Get enhanced job recommendations using content-aware matching
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getEnhancedJobRecommendations(int $userId, int $limit = 10): Collection
    {
        $user = User::with('jobSeekerProfile')->find($userId);
        if (!$user || !$user->jobSeekerProfile) {
            return collect([]);
        }

        $profile = $user->jobSeekerProfile;
        $preferredCategories = $this->ensureArray($profile->preferred_categories);

        if (empty($preferredCategories)) {
            // Return recent jobs if no preferences set
            return Job::where('status', 1)
                ->with(['category', 'jobType', 'employer.employerProfile'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        }

        // Get all active jobs with content analysis
        $allJobs = Job::where('status', 1)
            ->with(['category', 'jobType', 'employer.employerProfile'])
            ->get();

        if ($allJobs->isEmpty()) {
            return collect([]);
        }

        // Score each job using enhanced matching
        $scoredJobs = $this->scoreJobsForUser($allJobs, $user, $preferredCategories);

        // Apply diversity filtering
        $diverseJobs = $this->applyDiversityFilter($scoredJobs, $limit * 2);

        return $diverseJobs
            ->sortByDesc('enhanced_score')
            ->take($limit)
            ->values();
    }

    /**
     * Score jobs for a user using dual matching (category + content)
     */
    protected function scoreJobsForUser(Collection $jobs, User $user, array $preferredCategories): Collection
    {
        $profile = $user->jobSeekerProfile;
        $preferredJobTypes = $this->ensureArray($profile->preferred_job_types);
        $preferredLocations = $this->ensureArray($profile->preferred_locations);
        $userSkills = $this->ensureArray($profile->skills);

        // Get preferred category keys for content matching
        $preferredCategoryKeys = [];
        foreach ($preferredCategories as $catId) {
            $key = $this->contentAnalysis->getCategoryKeyById((int)$catId);
            if ($key) {
                $preferredCategoryKeys[] = $key;
            }
        }

        return $jobs->map(function($job) use (
            $preferredCategories,
            $preferredCategoryKeys,
            $preferredJobTypes,
            $preferredLocations,
            $userSkills,
            $profile
        ) {
            $score = 0;
            $matchReasons = [];

            // ===== 1. EMPLOYER'S CATEGORY MATCH (Traditional) =====
            if (in_array($job->category_id, $preferredCategories)) {
                $score += 30 * $this->featureWeights['category_match'];
                $matchReasons[] = 'category_match';
            }

            // ===== 2. CONTENT-BASED CATEGORY MATCH (NEW - Key improvement) =====
            $inferredCategories = $this->contentAnalysis->inferJobCategories($job);
            $contentMatchScore = 0;

            foreach ($preferredCategoryKeys as $prefKey) {
                if (isset($inferredCategories[$prefKey])) {
                    $inferredScore = $inferredCategories[$prefKey]['score'];
                    $confidence = $inferredCategories[$prefKey]['confidence'];

                    if ($confidence === 'high') {
                        $contentMatchScore += $inferredScore * 50;
                        $matchReasons[] = "content_high_{$prefKey}";
                    } elseif ($confidence === 'medium') {
                        $contentMatchScore += $inferredScore * 35;
                        $matchReasons[] = "content_medium_{$prefKey}";
                    } elseif ($confidence === 'low' && $inferredScore >= 0.15) {
                        $contentMatchScore += $inferredScore * 20;
                        $matchReasons[] = "content_low_{$prefKey}";
                    }
                }
            }
            $score += $contentMatchScore * $this->featureWeights['content_category_score'];

            // ===== 3. ROLE TYPE MATCHING =====
            $roleTypeScores = $this->calculateRoleTypeScores($job);
            $userRolePreference = $this->inferUserRolePreference($userSkills, $preferredCategoryKeys);

            foreach ($userRolePreference as $roleType => $preference) {
                if (isset($roleTypeScores[$roleType]) && $preference > 0) {
                    $score += $roleTypeScores[$roleType] * $preference * 20 * $this->featureWeights['role_type_score'];
                    if ($roleTypeScores[$roleType] > 0.5) {
                        $matchReasons[] = "role_type_{$roleType}";
                    }
                }
            }

            // ===== 4. SKILLS SIMILARITY =====
            $skillMatch = $this->contentAnalysis->calculateSkillMatch(
                User::find($profile->user_id),
                $job
            );
            $score += $skillMatch['score'] * 30 * $this->featureWeights['skill_similarity'];
            if ($skillMatch['score'] > 0.3) {
                $matchReasons[] = 'skills_match';
            }

            // ===== 5. EXPERIENCE LEVEL MATCH =====
            $expMatch = $this->calculateExperienceMatch($profile, $job);
            $score += $expMatch * 15 * $this->featureWeights['experience_match'];
            if ($expMatch > 0.7) {
                $matchReasons[] = 'experience_match';
            }

            // ===== 6. SALARY MATCH =====
            $salaryMatch = $this->calculateSalaryMatch($profile, $job);
            $score += $salaryMatch * 15 * $this->featureWeights['salary_match'];
            if ($salaryMatch > 0.6) {
                $matchReasons[] = 'salary_match';
            }

            // ===== 7. LOCATION MATCH =====
            $locationMatch = $this->calculateLocationMatch($preferredLocations, $job->location);
            $score += $locationMatch * 10 * $this->featureWeights['location_match'];
            if ($locationMatch > 0.7) {
                $matchReasons[] = 'location_match';
            }

            // ===== 8. JOB TYPE MATCH =====
            if (!empty($preferredJobTypes) && in_array($job->job_type_id, $preferredJobTypes)) {
                $score += 10 * $this->featureWeights['job_type_match'];
                $matchReasons[] = 'job_type_match';
            }

            // ===== 9. JOB FRESHNESS BOOST =====
            $daysOld = now()->diffInDays($job->created_at);
            $freshnessBoost = 1.0;
            if ($daysOld <= 3) {
                $freshnessBoost = 1.3;
                $matchReasons[] = 'very_fresh';
            } elseif ($daysOld <= 7) {
                $freshnessBoost = 1.2;
                $matchReasons[] = 'fresh';
            } elseif ($daysOld <= 14) {
                $freshnessBoost = 1.1;
            }
            $score *= $freshnessBoost;

            // ===== 10. REMOTE WORK PREFERENCE =====
            if (($profile->open_to_remote ?? false) && ($job->is_remote ?? false)) {
                $score += 5 * $this->featureWeights['is_remote'];
                $matchReasons[] = 'remote_match';
            }

            // Store results
            $job->enhanced_score = round($score, 2);
            $job->match_reasons = $matchReasons;
            $job->inferred_categories = array_slice($inferredCategories, 0, 3, true);
            $job->skill_match = $skillMatch;
            $job->category_mismatch = $this->contentAnalysis->detectCategoryMismatch($job);

            return $job;
        });
    }

    /**
     * Calculate role type scores for a job
     */
    protected function calculateRoleTypeScores(Job $job): array
    {
        $text = strtolower($job->title . ' ' . $job->description . ' ' . $job->requirements);
        $scores = [];

        foreach ($this->roleTypes as $roleType => $indicators) {
            $score = 0;
            $maxScore = count($indicators['keywords']) + count($indicators['skills']);

            foreach ($indicators['keywords'] as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score += 1;
                }
            }

            foreach ($indicators['skills'] as $skill) {
                if (strpos($text, $skill) !== false) {
                    $score += 1;
                }
            }

            $scores[$roleType] = $maxScore > 0 ? $score / $maxScore : 0;
        }

        return $scores;
    }

    /**
     * Infer user's role type preference from skills and categories
     */
    protected function inferUserRolePreference(array $userSkills, array $preferredCategoryKeys): array
    {
        $preferences = array_fill_keys(array_keys($this->roleTypes), 0);
        $userSkillsText = strtolower(implode(' ', $userSkills));

        // Infer from skills
        foreach ($this->roleTypes as $roleType => $indicators) {
            foreach ($indicators['skills'] as $skill) {
                if (strpos($userSkillsText, $skill) !== false) {
                    $preferences[$roleType] += 0.3;
                }
            }
        }

        // Infer from preferred categories
        $categoryToRoleMapping = [
            'information_technology' => 'technical',
            'administrative_clerical' => 'administrative',
            'customer_service' => 'customer_facing',
            'sales_marketing' => 'customer_facing',
            'creative_design' => 'creative',
            'engineering' => 'technical',
            'manufacturing_production' => 'manual_labor',
            'construction_trades' => 'manual_labor',
            'human_resources' => 'administrative',
            'accounting_finance' => 'administrative'
        ];

        foreach ($preferredCategoryKeys as $catKey) {
            if (isset($categoryToRoleMapping[$catKey])) {
                $roleType = $categoryToRoleMapping[$catKey];
                $preferences[$roleType] += 0.5;
            }
        }

        // Normalize to 0-1
        $maxPref = max($preferences) ?: 1;
        foreach ($preferences as &$pref) {
            $pref = min(1.0, $pref / $maxPref);
        }

        return $preferences;
    }

    /**
     * Calculate experience level match
     */
    protected function calculateExperienceMatch($profile, Job $job): float
    {
        $userExp = (int) ($profile->total_experience_years ?? 0);
        $jobExp = $this->extractExperienceLevel($job->requirements ?? '');

        if ($jobExp === 0) {
            return 1.0; // Entry level matches anyone
        }

        $diff = abs($userExp - $jobExp);

        if ($diff === 0) return 1.0;
        if ($diff <= 1) return 0.9;
        if ($diff <= 2) return 0.7;
        if ($diff <= 3) return 0.5;
        if ($diff <= 5) return 0.3;
        return 0.1;
    }

    /**
     * Extract experience level from requirements text
     */
    protected function extractExperienceLevel(?string $requirements): int
    {
        if (empty($requirements)) return 0;

        $requirements = strtolower($requirements);

        // Pattern: "3-5 years"
        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)\s*years?/i', $requirements, $matches)) {
            return (int)(($matches[1] + $matches[2]) / 2);
        }

        // Pattern: "5+ years"
        if (preg_match('/(\d+)\s*(?:\+|or more)\s*years?/i', $requirements, $matches)) {
            return (int)$matches[1];
        }

        // Pattern: "3 years"
        if (preg_match('/(\d+)\s*years?/i', $requirements, $matches)) {
            return (int)$matches[1];
        }

        // Keywords
        if (strpos($requirements, 'senior') !== false || strpos($requirements, 'lead') !== false) {
            return 5;
        }
        if (strpos($requirements, 'mid') !== false || strpos($requirements, 'intermediate') !== false) {
            return 3;
        }
        if (strpos($requirements, 'junior') !== false) {
            return 2;
        }
        if (strpos($requirements, 'entry') !== false || strpos($requirements, 'fresh') !== false) {
            return 0;
        }

        return 2; // Default to junior/entry
    }

    /**
     * Calculate salary match score
     */
    protected function calculateSalaryMatch($profile, Job $job): float
    {
        $userMin = (float) ($profile->expected_salary_min ?? 0);
        $userMax = (float) ($profile->expected_salary_max ?? 0);

        $jobSalary = $this->parseSalaryRange($job->salary_range ?? '');
        $jobMin = $jobSalary['min'];
        $jobMax = $jobSalary['max'];

        // If no salary info, return neutral score
        if (($userMin === 0.0 && $userMax === 0.0) || ($jobMin === 0.0 && $jobMax === 0.0)) {
            return 0.5;
        }

        // Check overlap
        if ($jobMax >= $userMin && $jobMin <= $userMax) {
            // Calculate overlap ratio
            $overlapMin = max($userMin, $jobMin);
            $overlapMax = min($userMax, $jobMax);
            $overlap = $overlapMax - $overlapMin;
            $userRange = $userMax - $userMin;

            if ($userRange > 0) {
                return min(1.0, $overlap / $userRange);
            }
            return 1.0;
        }

        // No overlap - calculate how far off
        if ($jobMax < $userMin) {
            // Job pays less than expected
            $gap = $userMin - $jobMax;
            $gapRatio = $gap / $userMin;
            return max(0.1, 1 - $gapRatio);
        }

        // Job pays more than expected (usually good)
        return 0.8;
    }

    /**
     * Parse salary range from string
     */
    protected function parseSalaryRange(?string $salaryString): array
    {
        if (empty($salaryString)) {
            return ['min' => 0, 'max' => 0];
        }

        preg_match_all('/[\d,]+/', $salaryString, $matches);
        $numbers = array_map(function($n) {
            return (float) str_replace(',', '', $n);
        }, $matches[0]);

        if (count($numbers) >= 2) {
            return ['min' => min($numbers), 'max' => max($numbers)];
        } elseif (count($numbers) === 1) {
            return ['min' => $numbers[0], 'max' => $numbers[0]];
        }

        return ['min' => 0, 'max' => 0];
    }

    /**
     * Calculate location match score
     */
    protected function calculateLocationMatch(array $preferredLocations, ?string $jobLocation): float
    {
        if (empty($preferredLocations) || empty($jobLocation)) {
            return 0.5; // Neutral
        }

        $jobLocation = strtolower($jobLocation);

        foreach ($preferredLocations as $preferred) {
            $preferred = strtolower($preferred);

            // Exact match
            if ($jobLocation === $preferred) {
                return 1.0;
            }

            // Partial match (city within region or vice versa)
            if (strpos($jobLocation, $preferred) !== false || strpos($preferred, $jobLocation) !== false) {
                return 0.8;
            }

            // Same major area (NCR cities)
            $ncrCities = ['manila', 'makati', 'quezon city', 'pasig', 'taguig', 'mandaluyong', 'bgc', 'ortigas'];
            $isJobInNCR = $this->containsAny($jobLocation, $ncrCities);
            $isPrefInNCR = $this->containsAny($preferred, $ncrCities);

            if ($isJobInNCR && $isPrefInNCR) {
                return 0.7;
            }
        }

        return 0.2; // Different location
    }

    /**
     * Check if text contains any of the given terms
     */
    protected function containsAny(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            if (strpos($text, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Apply diversity filter to avoid too many similar jobs
     */
    protected function applyDiversityFilter(Collection $jobs, int $limit): Collection
    {
        $diverseJobs = collect();
        $categoryCount = [];
        $maxPerCategory = max(3, (int)($limit / 3));

        foreach ($jobs->sortByDesc('enhanced_score') as $job) {
            $categoryId = $job->category_id;

            if (($categoryCount[$categoryId] ?? 0) < $maxPerCategory) {
                $diverseJobs->push($job);
                $categoryCount[$categoryId] = ($categoryCount[$categoryId] ?? 0) + 1;
            }

            if ($diverseJobs->count() >= $limit) {
                break;
            }
        }

        return $diverseJobs;
    }

    /**
     * Run enhanced K-means clustering on jobs
     */
    public function runEnhancedJobClustering(?int $k = null): array
    {
        $k = $k ?? $this->k;
        $cacheKey = "enhanced_kmeans_jobs_{$k}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function() use ($k) {
            $jobs = Job::where('status', 1)->get();

            if ($jobs->isEmpty() || $jobs->count() < $k) {
                return ['clusters' => [], 'centroids' => [], 'metrics' => []];
            }

            // Extract enhanced features
            $jobFeatures = $jobs->map(function($job) {
                return $this->extractEnhancedJobFeatures($job);
            })->toArray();

            // Run K-means with k-means++ initialization
            $result = $this->runKMeansPlusPlus($jobFeatures, $k);

            // Add job IDs to clusters
            foreach ($result['clusters'] as $clusterIdx => &$cluster) {
                foreach ($cluster as &$point) {
                    $point['job_id'] = $jobs[$point['index']]->id ?? null;
                }
            }

            // Calculate cluster quality metrics
            $result['metrics'] = $this->calculateClusterMetrics($result, $jobFeatures);

            return $result;
        });
    }

    /**
     * Extract enhanced features for a job
     */
    protected function extractEnhancedJobFeatures(Job $job): array
    {
        // Get inferred categories
        $inferredCategories = $this->contentAnalysis->inferJobCategories($job);
        $topCategories = array_slice($inferredCategories, 0, 3, true);

        // Get role type scores
        $roleTypeScores = $this->calculateRoleTypeScores($job);

        // Build feature vector
        $features = [
            // Content-based category scores (most important)
            'inferred_cat_1_score' => $topCategories[array_keys($topCategories)[0]]['score'] ?? 0,
            'inferred_cat_2_score' => $topCategories[array_keys($topCategories)[1] ?? 0]['score'] ?? 0,
            'inferred_cat_3_score' => $topCategories[array_keys($topCategories)[2] ?? 0]['score'] ?? 0,

            // Role type scores
            'role_technical' => $roleTypeScores['technical'] ?? 0,
            'role_administrative' => $roleTypeScores['administrative'] ?? 0,
            'role_customer_facing' => $roleTypeScores['customer_facing'] ?? 0,
            'role_creative' => $roleTypeScores['creative'] ?? 0,
            'role_management' => $roleTypeScores['management'] ?? 0,
            'role_manual_labor' => $roleTypeScores['manual_labor'] ?? 0,

            // Traditional features
            'experience_level' => $this->extractExperienceLevel($job->requirements) / 10, // Normalize to 0-1
            'salary_normalized' => $this->normalizeSalary($job->salary_range),
            'is_remote' => (float) ($job->is_remote ?? 0),
            'job_type_id' => (float) $job->job_type_id / 10, // Normalize

            // Freshness
            'freshness' => max(0, 1 - (now()->diffInDays($job->created_at) / 90)),
        ];

        return $features;
    }

    /**
     * Normalize salary to 0-1 range
     */
    protected function normalizeSalary(?string $salaryRange): float
    {
        $parsed = $this->parseSalaryRange($salaryRange);
        $avgSalary = ($parsed['min'] + $parsed['max']) / 2;

        // Normalize assuming max salary around 200,000
        return min(1.0, $avgSalary / 200000);
    }

    /**
     * Run K-means with K-means++ initialization
     */
    protected function runKMeansPlusPlus(array $data, int $k): array
    {
        if (empty($data) || count($data) < $k) {
            return ['clusters' => [], 'centroids' => []];
        }

        // K-means++ initialization
        $centroids = $this->initializeCentroidsKMeansPlusPlus($data, $k);
        $prevCentroids = [];
        $clusters = [];

        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $clusters = array_fill(0, $k, []);

            // Assignment step
            foreach ($data as $index => $point) {
                $distances = [];
                foreach ($centroids as $centroidIdx => $centroid) {
                    $distances[$centroidIdx] = $this->calculateWeightedDistance($point, $centroid);
                }
                $closestCluster = array_keys($distances, min($distances))[0];
                $clusters[$closestCluster][] = ['point' => $point, 'index' => $index];
            }

            $prevCentroids = $centroids;

            // Update step
            foreach ($clusters as $i => $cluster) {
                if (!empty($cluster)) {
                    $points = array_column($cluster, 'point');
                    $centroids[$i] = $this->calculateMean($points);
                }
            }

            // Check convergence
            if ($this->hasConverged($prevCentroids, $centroids)) {
                break;
            }
        }

        return [
            'clusters' => $clusters,
            'centroids' => $centroids,
            'iterations' => $iteration ?? 0
        ];
    }

    /**
     * Initialize centroids using K-means++ algorithm
     */
    protected function initializeCentroidsKMeansPlusPlus(array $data, int $k): array
    {
        $centroids = [];

        // Choose first centroid randomly
        $centroids[] = $data[array_rand($data)];

        // Choose remaining centroids
        for ($i = 1; $i < $k; $i++) {
            $distances = [];

            foreach ($data as $point) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) {
                    $dist = $this->calculateWeightedDistance($point, $centroid);
                    $minDist = min($minDist, $dist);
                }
                $distances[] = $minDist * $minDist; // Square the distance
            }

            // Choose next centroid with probability proportional to D(x)^2
            $totalDist = array_sum($distances);
            if ($totalDist == 0) break;

            $rand = mt_rand() / mt_getrandmax() * $totalDist;
            $cumulative = 0;

            foreach ($distances as $idx => $dist) {
                $cumulative += $dist;
                if ($cumulative >= $rand) {
                    $centroids[] = $data[$idx];
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Calculate weighted Euclidean distance
     */
    protected function calculateWeightedDistance(array $point1, array $point2): float
    {
        $distance = 0;

        foreach ($point1 as $key => $value) {
            if (isset($point2[$key]) && is_numeric($value) && is_numeric($point2[$key])) {
                $weight = $this->getFeatureWeight($key);
                $diff = $value - $point2[$key];
                $distance += $weight * $diff * $diff;
            }
        }

        return sqrt($distance);
    }

    /**
     * Get weight for a specific feature
     */
    protected function getFeatureWeight(string $feature): float
    {
        $weights = [
            'inferred_cat_1_score' => 4.0,
            'inferred_cat_2_score' => 2.0,
            'inferred_cat_3_score' => 1.0,
            'role_technical' => 3.0,
            'role_administrative' => 3.0,
            'role_customer_facing' => 3.0,
            'role_creative' => 3.0,
            'role_management' => 2.5,
            'role_manual_labor' => 3.0,
            'experience_level' => 2.0,
            'salary_normalized' => 1.5,
            'is_remote' => 0.5,
            'job_type_id' => 1.0,
            'freshness' => 0.5
        ];

        return $weights[$feature] ?? 1.0;
    }

    /**
     * Calculate mean of points
     */
    protected function calculateMean(array $points): array
    {
        if (empty($points)) return [];

        $mean = [];
        $count = count($points);

        foreach ($points[0] as $key => $value) {
            if (is_numeric($value)) {
                $sum = array_sum(array_column($points, $key));
                $mean[$key] = $sum / $count;
            }
        }

        return $mean;
    }

    /**
     * Check if centroids have converged
     */
    protected function hasConverged(array $prevCentroids, array $centroids): bool
    {
        if (empty($prevCentroids)) return false;

        foreach ($centroids as $i => $centroid) {
            if (!isset($prevCentroids[$i])) continue;

            $distance = $this->calculateWeightedDistance($prevCentroids[$i], $centroid);
            if ($distance > $this->convergenceThreshold) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate cluster quality metrics
     */
    protected function calculateClusterMetrics(array $result, array $data): array
    {
        $clusters = $result['clusters'];
        $centroids = $result['centroids'];

        // Calculate inertia (within-cluster sum of squares)
        $inertia = 0;
        foreach ($clusters as $i => $cluster) {
            foreach ($cluster as $pointData) {
                $distance = $this->calculateWeightedDistance($pointData['point'], $centroids[$i]);
                $inertia += $distance * $distance;
            }
        }

        // Calculate silhouette score (simplified)
        $silhouetteScore = $this->calculateSilhouetteScore($clusters, $centroids);

        // Cluster sizes
        $clusterSizes = array_map(fn($c) => count($c), $clusters);

        return [
            'inertia' => round($inertia, 4),
            'silhouette_score' => round($silhouetteScore, 4),
            'cluster_sizes' => $clusterSizes,
            'total_points' => count($data),
            'k' => count($centroids)
        ];
    }

    /**
     * Calculate simplified silhouette score
     */
    protected function calculateSilhouetteScore(array $clusters, array $centroids): float
    {
        if (count($clusters) < 2) return 0;

        $scores = [];

        foreach ($clusters as $i => $cluster) {
            if (empty($cluster)) continue;

            foreach ($cluster as $pointData) {
                $point = $pointData['point'];

                // a(i): average distance to points in same cluster
                $a = 0;
                $sameClusterCount = count($cluster) - 1;
                if ($sameClusterCount > 0) {
                    foreach ($cluster as $otherPointData) {
                        if ($otherPointData !== $pointData) {
                            $a += $this->calculateWeightedDistance($point, $otherPointData['point']);
                        }
                    }
                    $a /= $sameClusterCount;
                }

                // b(i): minimum average distance to points in other clusters
                $b = PHP_FLOAT_MAX;
                foreach ($clusters as $j => $otherCluster) {
                    if ($i !== $j && !empty($otherCluster)) {
                        $avgDist = 0;
                        foreach ($otherCluster as $otherPointData) {
                            $avgDist += $this->calculateWeightedDistance($point, $otherPointData['point']);
                        }
                        $avgDist /= count($otherCluster);
                        $b = min($b, $avgDist);
                    }
                }

                // Silhouette for this point
                if (max($a, $b) > 0) {
                    $scores[] = ($b - $a) / max($a, $b);
                }
            }
        }

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    /**
     * Find optimal K using elbow method
     */
    public function findOptimalK(int $maxK = 10): array
    {
        $jobs = Job::where('status', 1)->get();
        $data = $jobs->map(fn($job) => $this->extractEnhancedJobFeatures($job))->toArray();

        if (count($data) < 3) {
            return ['optimal_k' => min(count($data), 3), 'inertias' => [], 'method' => 'default'];
        }

        $maxK = min($maxK, count($data) - 1);
        $inertias = [];

        for ($k = 2; $k <= $maxK; $k++) {
            $result = $this->runKMeansPlusPlus($data, $k);
            $metrics = $this->calculateClusterMetrics($result, $data);
            $inertias[$k] = $metrics['inertia'];
        }

        // Find elbow point
        $optimalK = $this->findElbowPoint($inertias);

        return [
            'optimal_k' => $optimalK,
            'inertias' => $inertias,
            'method' => 'elbow'
        ];
    }

    /**
     * Find elbow point in inertia curve
     */
    protected function findElbowPoint(array $inertias): int
    {
        if (count($inertias) < 3) {
            return array_key_first($inertias) ?? 3;
        }

        $keys = array_keys($inertias);
        $values = array_values($inertias);
        $n = count($values);

        $maxCurvature = 0;
        $elbowK = $keys[1];

        for ($i = 1; $i < $n - 1; $i++) {
            // Calculate curvature using second derivative
            $curvature = abs($values[$i - 1] - 2 * $values[$i] + $values[$i + 1]);
            if ($curvature > $maxCurvature) {
                $maxCurvature = $curvature;
                $elbowK = $keys[$i];
            }
        }

        return $elbowK;
    }

    /**
     * Get labor market insights
     */
    public function getLaborMarketInsights(): array
    {
        return Cache::remember('enhanced_labor_market_insights', $this->cacheTimeout, function() {
            $jobs = Job::where('status', 1)->get();
            $users = User::where('role', 'jobseeker')
                ->whereHas('jobSeekerProfile')
                ->with('jobSeekerProfile')
                ->get();

            // Category demand analysis
            $categoryDemand = $this->analyzeCategoryDemand($jobs);

            // Skills demand
            $skillsDemand = $this->analyzeSkillsDemand($jobs);

            // Category mismatch analysis
            $mismatchAnalysis = $this->analyzeCategoryMismatches($jobs);

            // Supply-demand ratio
            $supplyDemand = [
                'total_jobs' => $jobs->count(),
                'total_candidates' => $users->count(),
                'ratio' => $users->count() > 0 ? round($jobs->count() / $users->count(), 2) : 0
            ];

            return [
                'category_demand' => $categoryDemand,
                'skills_demand' => $skillsDemand,
                'category_mismatch_analysis' => $mismatchAnalysis,
                'supply_demand' => $supplyDemand,
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * Analyze category demand
     */
    protected function analyzeCategoryDemand(Collection $jobs): array
    {
        $demand = [];

        foreach ($jobs as $job) {
            $categoryId = $job->category_id;
            $demand[$categoryId] = ($demand[$categoryId] ?? 0) + 1;
        }

        arsort($demand);
        return $demand;
    }

    /**
     * Analyze skills demand
     */
    protected function analyzeSkillsDemand(Collection $jobs): array
    {
        $skillCounts = [];

        foreach ($jobs as $job) {
            $skills = $this->contentAnalysis->extractJobSkills($job);
            foreach ($skills as $skill => $data) {
                $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
            }
        }

        arsort($skillCounts);
        return array_slice($skillCounts, 0, 20, true);
    }

    /**
     * Analyze category mismatches
     */
    protected function analyzeCategoryMismatches(Collection $jobs): array
    {
        $mismatches = [];
        $mismatchCount = 0;

        foreach ($jobs as $job) {
            $mismatch = $this->contentAnalysis->detectCategoryMismatch($job);
            if ($mismatch['has_mismatch']) {
                $mismatchCount++;
                $mismatches[] = [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'employer_category' => $mismatch['employer_category_key'],
                    'inferred_category' => $mismatch['inferred_category_key'],
                    'confidence' => $mismatch['confidence']
                ];
            }
        }

        return [
            'total_mismatches' => $mismatchCount,
            'mismatch_rate' => $jobs->count() > 0 ? round($mismatchCount / $jobs->count() * 100, 1) : 0,
            'mismatched_jobs' => array_slice($mismatches, 0, 10)
        ];
    }

    /**
     * Clear all caches
     */
    public function clearCache(): void
    {
        Cache::forget("enhanced_kmeans_jobs_{$this->k}");
        Cache::forget('enhanced_labor_market_insights');
        $this->contentAnalysis->clearCache();
    }

    /**
     * Ensure value is array
     */
    protected function ensureArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
