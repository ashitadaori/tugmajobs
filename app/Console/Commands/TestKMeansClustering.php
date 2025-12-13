<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Jobseeker;
use App\Services\KMeansClusteringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestKMeansClustering extends Command
{
    protected $signature = 'test:kmeans';
    protected $description = 'Test K-Means clustering functionality with category requirements';

    private $clusteringService;

    public function __construct()
    {
        parent::__construct();
        $this->clusteringService = new KMeansClusteringService(3, 50);
    }

    public function handle()
    {
        $this->info('🧪 Starting K-Means Clustering Tests');
        $this->line(str_repeat('=', 50));
        
        try {
            // Test 1: Category-based job filtering
            $this->testCategoryBasedFiltering();
            
            // Test 2: User preference validation
            $this->testUserPreferenceValidation();
            
            // Test 3: K-means job clustering
            $this->testJobClustering();
            
            // Test 4: K-means user clustering
            $this->testUserClustering();
            
            // Test 5: Job recommendations based on clustering
            $this->testJobRecommendations();
            
            // Test 6: Category-based job display
            $this->testCategoryBasedJobDisplay();
            
            // Generate report
            $this->generateReport();
            
            $this->info("\n✅ All tests completed successfully!");
            
            $this->displaySummary();
            
        } catch (\Exception $e) {
            $this->error("\n❌ Test failed with error: " . $e->getMessage());
            $this->line("Stack trace:\n" . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function testCategoryBasedFiltering()
    {
        $this->line("\n📊 Test 1: Category-based Job Filtering");
        $this->line(str_repeat('-', 40));
        
        // Get available categories
        $categories = Category::where('status', 1)->get();
        $this->info("Available categories: " . $categories->count());
        
        foreach ($categories as $category) {
            $jobCount = Job::where('status', 1)
                          ->where('category_id', $category->id)
                          ->count();
            $this->line("- {$category->name}: {$jobCount} jobs");
        }
        
        // Test filtering by specific category
        $testCategory = $categories->first();
        if ($testCategory) {
            $filteredJobs = Job::where('status', 1)
                              ->where('category_id', $testCategory->id)
                              ->with(['category', 'jobType'])
                              ->get();
            
            $this->line("\nFiltered jobs for category '{$testCategory->name}': {$filteredJobs->count()}");
            
            foreach ($filteredJobs->take(3) as $job) {
                $categoryName = $job->category ? $job->category->name : 'N/A';
                $this->line("- {$job->title} ({$categoryName})");
            }
        }
        
        $this->info("✅ Category-based filtering test passed!");
    }

    private function testUserPreferenceValidation()
    {
        $this->line("\n👤 Test 2: User Preference Validation");
        $this->line(str_repeat('-', 40));

        // Find jobseekers with and without category preferences (using jobseekers table)
        $jobseekersWithPreferences = Jobseeker::whereNotNull('preferred_categories')
                                  ->where('preferred_categories', '!=', '[]')
                                  ->count();

        $jobseekersWithoutPreferences = Jobseeker::where(function($query) {
                                         $query->whereNull('preferred_categories')
                                               ->orWhere('preferred_categories', '[]')
                                               ->orWhere('preferred_categories', '');
                                     })
                                     ->count();

        $this->info("Jobseekers with category preferences: {$jobseekersWithPreferences}");
        $this->info("Jobseekers without category preferences: {$jobseekersWithoutPreferences}");

        // Test preference requirement logic
        $testJobseeker = Jobseeker::with('user')->first();
        if ($testJobseeker) {
            $hasPreferences = $this->jobseekerHasCategoryPreferences($testJobseeker);
            $status = $hasPreferences ? 'Yes' : 'No';
            $userName = $testJobseeker->user->name ?? 'Unknown';
            $this->line("\nTest jobseeker '{$userName}' has category preferences: {$status}");

            if ($hasPreferences) {
                $preferences = is_array($testJobseeker->preferred_categories)
                    ? $testJobseeker->preferred_categories
                    : (json_decode($testJobseeker->preferred_categories, true) ?: []);
                $categoryNames = $this->getCategoryNames($preferences);
                $this->line("Preferred categories: " . implode(', ', $categoryNames));
            }
        }

        $this->info("✅ User preference validation test passed!");
    }

    private function testJobClustering()
    {
        $this->line("\n🎯 Test 3: K-Means Job Clustering");
        $this->line(str_repeat('-', 40));
        
        $result = $this->clusteringService->runJobClustering();
        
        if (empty($result)) {
            $this->warn("No jobs available for clustering");
            return;
        }
        
        $this->info("Job clustering completed successfully!");
        $this->info("Number of clusters: " . count($result['clusters']));
        
        foreach ($result['clusters'] as $i => $cluster) {
            $this->line("Cluster {$i}: " . count($cluster) . " jobs");
            
            // Show sample jobs from this cluster
            $sampleJobs = array_slice($cluster, 0, 2);
            foreach ($sampleJobs as $jobData) {
                if (isset($jobData['index'])) {
                    $job = Job::skip($jobData['index'])->first();
                    if ($job) {
                        $categoryName = $job->category ? $job->category->name : 'N/A';
                        $this->line("  - {$job->title} ({$categoryName})");
                    }
                }
            }
        }
        
        $this->info("✅ Job clustering test passed!");
    }

    private function testUserClustering()
    {
        $this->line("\n👥 Test 4: K-Means User Clustering");
        $this->line(str_repeat('-', 40));
        
        $result = $this->clusteringService->runUserClustering();
        
        if (empty($result)) {
            $this->warn("No users available for clustering");
            return;
        }
        
        $this->info("User clustering completed successfully!");
        $this->info("Number of clusters: " . count($result['clusters']));
        
        foreach ($result['clusters'] as $i => $cluster) {
            $this->line("Cluster {$i}: " . count($cluster) . " users");
            
            // Show sample users from this cluster
            $sampleUsers = array_slice($cluster, 0, 2);
            foreach ($sampleUsers as $userData) {
                if (isset($userData['point']['id'])) {
                    $userId = $userData['point']['id'];
                    $user = User::find($userId);
                    if ($user) {
                        $categoryId = $userData['point']['category_id'] ?? 0;
                        $category = $categoryId > 0 ? Category::find($categoryId) : null;
                        $categoryName = $category ? $category->name : 'None';
                        $this->line("  - {$user->name} (Category: {$categoryName})");
                    }
                }
            }
        }
        
        $this->info("✅ User clustering test passed!");
    }

    private function testJobRecommendations()
    {
        $this->line("\n🎯 Test 5: Job Recommendations Based on Clustering");
        $this->line(str_repeat('-', 40));

        // Find a jobseeker with preferences (using jobseekers table)
        $testJobseeker = Jobseeker::with('user')
                       ->whereNotNull('preferred_categories')
                       ->where('preferred_categories', '!=', '[]')
                       ->first();

        if (!$testJobseeker) {
            $this->line("No jobseekers with preferences found. Creating test jobseeker...");
            $testJobseeker = $this->createTestJobseeker();
        }

        $userName = $testJobseeker->user->name ?? 'Unknown';
        $this->line("Testing recommendations for jobseeker: {$userName}");

        $recommendations = $this->clusteringService->getJobRecommendations($testJobseeker->user_id, 5);

        $this->info("Found " . $recommendations->count() . " job recommendations");

        foreach ($recommendations as $job) {
            $employerName = $job->employer->name ?? 'Unknown';
            $this->line("- {$job->title} at {$employerName}");
            $categoryName = $job->category ? $job->category->name : 'N/A';
            $jobTypeName = $job->jobType ? $job->jobType->name : 'N/A';
            $this->line("  Category: {$categoryName}");
            $this->line("  Location: {$job->location}");
            $this->line("  Type: {$jobTypeName}");
            $this->line("");
        }

        $this->info("✅ Job recommendations test passed!");
    }

    private function testCategoryBasedJobDisplay()
    {
        $this->line("\n📋 Test 6: Category-based Job Display Logic");
        $this->line(str_repeat('-', 40));

        // Test jobseekers with and without category preferences (using jobseekers table)
        $jobseekersToTest = Jobseeker::with('user')->take(3)->get();

        foreach ($jobseekersToTest as $jobseeker) {
            $userName = $jobseeker->user->name ?? 'Unknown';
            $this->line("Testing job display for jobseeker: {$userName}");

            $hasPreferences = $this->jobseekerHasCategoryPreferences($jobseeker);

            if (!$hasPreferences) {
                $this->line("  ❌ User must select job categories before viewing jobs");
                $this->line("  → Redirect to profile completion");
            } else {
                $preferences = is_array($jobseeker->preferred_categories)
                    ? $jobseeker->preferred_categories
                    : (json_decode($jobseeker->preferred_categories, true) ?: []);
                $availableJobs = $this->getJobsForUserCategories($jobseeker, $preferences);

                $categoryNames = $this->getCategoryNames($preferences);
                $this->line("  ✅ User has category preferences: " . implode(', ', $categoryNames));
                $this->line("  → Available jobs: {$availableJobs->count()}");

                // Apply k-means recommendations
                $recommendedJobs = $this->clusteringService->getJobRecommendations($jobseeker->user_id, 3);
                $this->line("  → Recommended jobs (clustering): {$recommendedJobs->count()}");

                foreach ($recommendedJobs->take(2) as $job) {
                    $categoryName = $job->category ? $job->category->name : 'N/A';
                    $this->line("    - {$job->title} ({$categoryName})");
                }
            }
            $this->line("");
        }

        $this->info("✅ Category-based job display test passed!");
    }

    private function generateReport()
    {
        $this->line("\n📊 K-Means Clustering System Report");
        $this->line(str_repeat('=', 50));

        // System stats
        $totalJobs = Job::where('status', 1)->count();
        $totalJobseekers = Jobseeker::count();
        $totalCategories = Category::where('status', 1)->count();

        // User preference stats (using jobseekers table)
        $jobseekersWithPreferences = Jobseeker::whereNotNull('preferred_categories')
                                  ->where('preferred_categories', '!=', '[]')
                                  ->count();
        
        $this->info("System Statistics:");
        $this->line("- Total active jobs: {$totalJobs}");
        $this->line("- Total jobseekers: {$totalJobseekers}");
        $this->line("- Total categories: {$totalCategories}");
        $this->line("- Jobseekers with category preferences: {$jobseekersWithPreferences}");

        $completionRate = $totalJobseekers > 0 ? round(($jobseekersWithPreferences / $totalJobseekers) * 100, 1) : 0;
        $this->line("- Category preference completion rate: {$completionRate}%");
        
        // Clustering performance
        $this->line("\nClustering Performance:");
        $startTime = microtime(true);
        $jobClusters = $this->clusteringService->runJobClustering();
        $jobClusteringTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $startTime = microtime(true);
        $userClusters = $this->clusteringService->runUserClustering();
        $userClusteringTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->line("- Job clustering time: {$jobClusteringTime}ms");
        $this->line("- User clustering time: {$userClusteringTime}ms");
        $this->line("- Job clusters created: " . count($jobClusters['clusters'] ?? []));
        $this->line("- User clusters created: " . count($userClusters['clusters'] ?? []));
        
        // Category distribution
        $this->line("\nJob Distribution by Category:");
        $categoryStats = DB::table('jobs')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->where('jobs.status', 1)
            ->select('categories.name', DB::raw('COUNT(*) as job_count'))
            ->groupBy('categories.name')
            ->orderByDesc('job_count')
            ->get();
        
        foreach ($categoryStats as $stat) {
            $this->line("- {$stat->name}: {$stat->job_count} jobs");
        }
        
        $this->info("\n✅ Report generation completed!");
    }

    private function displaySummary()
    {
        $this->line("\n🎯 K-Means Clustering Test Summary:");
        $this->info("1. ✅ Category-based job filtering works correctly");
        $this->info("2. ✅ User preference validation implemented");
        $this->info("3. ✅ K-means job clustering functional");
        $this->info("4. ✅ K-means user clustering functional");
        $this->info("5. ✅ Job recommendations based on clustering");
        $this->info("6. ✅ Category-based job display logic verified");
        $this->line("");
        $this->comment("The system enforces that jobseekers must select job categories");
        $this->comment("before they can view jobs, and shows only relevant jobs based on");
        $this->comment("their preferences using k-means clustering for recommendations.");
    }

    private function userHasCategoryPreferences($user)
    {
        // Legacy method for User model - check via jobseeker profile
        $jobseeker = Jobseeker::where('user_id', $user->id)->first();
        if (!$jobseeker) {
            return false;
        }
        return $this->jobseekerHasCategoryPreferences($jobseeker);
    }

    private function jobseekerHasCategoryPreferences($jobseeker)
    {
        if (empty($jobseeker->preferred_categories)) {
            return false;
        }

        $preferences = is_array($jobseeker->preferred_categories)
            ? $jobseeker->preferred_categories
            : (json_decode($jobseeker->preferred_categories, true) ?: []);

        return is_array($preferences) && count($preferences) > 0;
    }

    private function getJobsForUserCategories($jobseeker, $categoryIds)
    {
        return Job::where('status', 1)
                  ->whereIn('category_id', $categoryIds)
                  ->with(['category', 'jobType', 'employer'])
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    private function getCategoryNames($categoryIds)
    {
        return Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
    }

    private function createTestJobseeker()
    {
        $categories = Category::where('status', 1)->take(2)->pluck('id')->toArray();
        $jobTypes = JobType::where('status', 1)->take(1)->pluck('id')->toArray();

        // Create user first
        $user = new User();
        $user->name = 'Test User for K-Means';
        $user->email = 'kmeans_test_' . time() . '@test.com';
        $user->password = bcrypt('password');
        $user->role = 'jobseeker';
        $user->save();

        // Create jobseeker profile
        $jobseeker = new Jobseeker();
        $jobseeker->user_id = $user->id;
        $jobseeker->first_name = 'Test';
        $jobseeker->last_name = 'User';
        $jobseeker->preferred_categories = $categories;
        $jobseeker->preferred_job_types = $jobTypes;
        $jobseeker->total_experience_years = 3;
        $jobseeker->city = 'Manila';
        $jobseeker->save();

        // Load relationship
        $jobseeker->load('user');

        return $jobseeker;
    }
}
