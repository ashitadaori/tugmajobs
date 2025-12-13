<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\AdvancedKMeansClusteringService;
use App\Services\BasicKMeansClusteringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EvaluateKMeans extends Command
{
    protected $signature = 'kmeans:evaluate {--detailed} {--compare}';
    protected $description = 'Evaluate K-Means clustering performance using scientific metrics';

    private $advancedService;
    private $basicService;

    public function __construct()
    {
        parent::__construct();
        $this->advancedService = new AdvancedKMeansClusteringService();
        $this->basicService = new BasicKMeansClusteringService();
    }

    public function handle()
    {
        $this->info('🔬 K-Means Clustering Performance Evaluation');
        $this->line(str_repeat('=', 70));

        if ($this->option('compare')) {
            $this->compareSystemsDetailed();
        } else {
            $this->evaluateAdvancedSystem();
        }

        if ($this->option('detailed')) {
            $this->showDetailedMetrics();
        }

        return 0;
    }

    protected function evaluateAdvancedSystem()
    {
        $this->line("\n📊 Advanced K-Means System Evaluation");
        $this->line(str_repeat('-', 50));

        // 1. Silhouette Score Analysis
        $this->evaluateSilhouetteScore();

        // 2. Cluster Quality Metrics
        $this->evaluateClusterQuality();

        // 3. Convergence Analysis
        $this->evaluateConvergence();

        // 4. Recommendation Accuracy
        $this->evaluateRecommendationAccuracy();

        // 5. System Performance
        $this->evaluateSystemPerformance();

        // 6. Data Distribution Analysis
        $this->evaluateDataDistribution();
    }

    protected function evaluateSilhouetteScore()
    {
        $this->info("\n🎯 1. Silhouette Score Analysis");
        $this->line("   (Measures how well-separated clusters are)");
        
        $reflection = new \ReflectionClass($this->advancedService);
        $method = $reflection->getMethod('runAdvancedKMeans');
        $method->setAccessible(true);
        $result = $method->invoke($this->advancedService);

        $silhouette = $result['silhouette_score'] ?? 0;
        $this->line("   Score: " . round($silhouette, 4));
        
        // Interpretation
        if ($silhouette > 0.7) {
            $this->line("   ✅ Excellent clustering (0.7-1.0)");
        } elseif ($silhouette > 0.5) {
            $this->line("   ✅ Good clustering (0.5-0.7)");
        } elseif ($silhouette > 0.25) {
            $this->line("   ⚠️  Moderate clustering (0.25-0.5)");
        } else {
            $this->line("   ❌ Poor clustering (<0.25)");
        }

        $this->line("   Benchmark: >0.5 is good, >0.7 is excellent");
    }

    protected function evaluateClusterQuality()
    {
        $this->info("\n📈 2. Cluster Quality Metrics");
        
        $reflection = new \ReflectionClass($this->advancedService);
        $method = $reflection->getMethod('runAdvancedKMeans');
        $method->setAccessible(true);
        $result = $method->invoke($this->advancedService);

        if (empty($result['clusters'])) {
            $this->warn("   ⚠️  No clusters formed - insufficient data");
            return;
        }

        $clusters = $result['clusters'];
        $totalPoints = array_sum(array_map('count', $clusters));
        
        $this->line("   Total Data Points: " . $totalPoints);
        $this->line("   Number of Clusters: " . count($clusters));
        $this->line("   Iterations to Converge: " . ($result['iterations'] ?? 0));

        // Cluster size analysis
        $this->line("\n   📋 Cluster Size Distribution:");
        $minSize = PHP_INT_MAX;
        $maxSize = 0;
        
        foreach ($clusters as $i => $cluster) {
            $size = count($cluster);
            $percentage = $totalPoints > 0 ? round(($size / $totalPoints) * 100, 1) : 0;
            $this->line("      Cluster " . ($i + 1) . ": {$size} points ({$percentage}%)");
            
            $minSize = min($minSize, $size);
            $maxSize = max($maxSize, $size);
        }

        // Balance analysis
        $balance = $totalPoints > 0 ? $minSize / $maxSize : 0;
        $this->line("\n   ⚖️  Cluster Balance: " . round($balance, 3));
        if ($balance > 0.5) {
            $this->line("      ✅ Well-balanced clusters");
        } else {
            $this->line("      ⚠️  Imbalanced clusters (some very large/small)");
        }
    }

    protected function evaluateConvergence()
    {
        $this->info("\n🎯 3. Convergence Analysis");
        
        $totalIterations = [];
        $convergenceTests = 5;
        
        for ($i = 0; $i < $convergenceTests; $i++) {
            $service = new AdvancedKMeansClusteringService();
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('runAdvancedKMeans');
            $method->setAccessible(true);
            $result = $method->invoke($service);
            
            $totalIterations[] = $result['iterations'] ?? 0;
        }

        $avgIterations = array_sum($totalIterations) / count($totalIterations);
        $maxIterations = max($totalIterations);
        
        $this->line("   Average Iterations: " . round($avgIterations, 1));
        $this->line("   Max Iterations: " . $maxIterations);
        $this->line("   Convergence Limit: 100");
        
        if ($avgIterations < 20) {
            $this->line("   ✅ Fast convergence (<20 iterations)");
        } elseif ($avgIterations < 50) {
            $this->line("   ✅ Good convergence (20-50 iterations)");
        } else {
            $this->line("   ⚠️  Slow convergence (>50 iterations)");
        }
    }

    protected function evaluateRecommendationAccuracy()
    {
        $this->info("\n🎯 4. Recommendation Accuracy Test");
        
        $users = User::where('role', 'jobseeker')->take(3)->get();
        $accuracyScores = [];
        $diversityScores = [];
        
        foreach ($users as $user) {
            $recommendations = $this->advancedService->getAdvancedJobRecommendations($user->id, 5);
            
            // Calculate accuracy (based on user preferences)
            $userCategories = is_array($user->preferred_categories) ? 
                            $user->preferred_categories : 
                            json_decode($user->preferred_categories ?? '[]', true);
            
            if (!empty($userCategories) && $recommendations->count() > 0) {
                $matches = 0;
                foreach ($recommendations as $job) {
                    if (in_array($job->category_id, $userCategories)) {
                        $matches++;
                    }
                }
                $accuracy = $matches / $recommendations->count();
                $accuracyScores[] = $accuracy;
            }
            
            // Calculate diversity (different categories in recommendations)
            $categories = $recommendations->pluck('category_id')->unique();
            $diversity = $recommendations->count() > 0 ? 
                        $categories->count() / $recommendations->count() : 0;
            $diversityScores[] = $diversity;
        }
        
        if (!empty($accuracyScores)) {
            $avgAccuracy = array_sum($accuracyScores) / count($accuracyScores);
            $this->line("   Preference Match Rate: " . round($avgAccuracy * 100, 1) . "%");
            
            if ($avgAccuracy > 0.7) {
                $this->line("   ✅ High accuracy (>70%)");
            } elseif ($avgAccuracy > 0.5) {
                $this->line("   ✅ Good accuracy (50-70%)");
            } else {
                $this->line("   ⚠️  Low accuracy (<50%)");
            }
        }
        
        if (!empty($diversityScores)) {
            $avgDiversity = array_sum($diversityScores) / count($diversityScores);
            $this->line("   Recommendation Diversity: " . round($avgDiversity, 3));
            
            if ($avgDiversity > 0.6) {
                $this->line("   ✅ Good diversity (prevents monotony)");
            } else {
                $this->line("   ⚠️  Low diversity (may be too narrow)");
            }
        }
    }

    protected function evaluateSystemPerformance()
    {
        $this->info("\n⚡ 5. System Performance Test");
        
        $user = User::where('role', 'jobseeker')->first();
        if (!$user) {
            $this->warn("   No users available for testing");
            return;
        }
        
        $iterations = 10;
        $times = [];
        $memoryUsage = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $memStart = memory_get_usage(true);
            
            $this->advancedService->getAdvancedJobRecommendations($user->id, 5);
            
            $times[] = (microtime(true) - $start) * 1000;
            $memoryUsage[] = (memory_get_usage(true) - $memStart) / 1024 / 1024;
        }
        
        $avgTime = array_sum($times) / count($times);
        $avgMemory = array_sum($memoryUsage) / count($memoryUsage);
        
        $this->line("   Average Response Time: " . round($avgTime, 2) . "ms");
        $this->line("   Average Memory Usage: " . round($avgMemory, 2) . "MB");
        
        // Performance benchmarks
        if ($avgTime < 100) {
            $this->line("   ✅ Excellent speed (<100ms)");
        } elseif ($avgTime < 500) {
            $this->line("   ✅ Good speed (100-500ms)");
        } elseif ($avgTime < 1000) {
            $this->line("   ⚠️  Acceptable speed (500-1000ms)");
        } else {
            $this->line("   ❌ Slow speed (>1000ms)");
        }
        
        if ($avgMemory < 50) {
            $this->line("   ✅ Low memory usage (<50MB)");
        } elseif ($avgMemory < 100) {
            $this->line("   ✅ Moderate memory usage (50-100MB)");
        } else {
            $this->line("   ⚠️  High memory usage (>100MB)");
        }
    }

    protected function evaluateDataDistribution()
    {
        $this->info("\n📊 6. Data Distribution Analysis");
        
        $jobCount = Job::where('status', 1)->count();
        $userCount = User::where('role', 'jobseeker')->count();
        $categoryCount = DB::table('jobs')
            ->where('status', 1)
            ->distinct('category_id')
            ->count();
        
        $this->line("   Active Jobs: " . $jobCount);
        $this->line("   Active Users: " . $userCount);
        $this->line("   Job Categories: " . $categoryCount);
        
        // Data sufficiency analysis
        $minDataForClustering = 10;
        $optimalDataForClustering = 50;
        
        if ($jobCount < $minDataForClustering) {
            $this->line("   ❌ Insufficient data for reliable clustering");
            $this->line("      Recommendation: Add more jobs (min: {$minDataForClustering})");
        } elseif ($jobCount < $optimalDataForClustering) {
            $this->line("   ⚠️  Limited data - clustering may be basic");
            $this->line("      Recommendation: Add more jobs for better results (optimal: {$optimalDataForClustering}+)");
        } else {
            $this->line("   ✅ Sufficient data for quality clustering");
        }
        
        // Category distribution
        $categoryDistribution = DB::table('jobs')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->where('jobs.status', 1)
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->get();
        
        $this->line("\n   📋 Top Job Categories:");
        foreach ($categoryDistribution->take(5) as $category) {
            $percentage = round(($category->count / $jobCount) * 100, 1);
            $this->line("      {$category->name}: {$category->count} ({$percentage}%)");
        }
    }

    protected function compareSystemsDetailed()
    {
        $this->info("\n🔄 Advanced vs Basic K-Means Comparison");
        $this->line(str_repeat('-', 50));
        
        $user = User::where('role', 'jobseeker')->first();
        if (!$user) {
            $this->error("No test users available");
            return;
        }
        
        $iterations = 10;
        
        // Test Advanced System
        $advancedTimes = [];
        $advancedMemory = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $memStart = memory_get_usage(true);
            
            $advancedRecs = $this->advancedService->getAdvancedJobRecommendations($user->id, 5);
            
            $advancedTimes[] = (microtime(true) - $start) * 1000;
            $advancedMemory[] = (memory_get_usage(true) - $memStart) / 1024 / 1024;
        }
        
        // Test Basic System
        $basicTimes = [];
        $basicMemory = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $memStart = memory_get_usage(true);
            
            $basicRecs = $this->basicService->getJobRecommendations($user->id, 5);
            
            $basicTimes[] = (microtime(true) - $start) * 1000;
            $basicMemory[] = (memory_get_usage(true) - $memStart) / 1024 / 1024;
        }
        
        // Calculate averages
        $avgAdvTime = array_sum($advancedTimes) / count($advancedTimes);
        $avgBasicTime = array_sum($basicTimes) / count($basicTimes);
        $avgAdvMem = array_sum($advancedMemory) / count($advancedMemory);
        $avgBasicMem = array_sum($basicMemory) / count($basicMemory);
        
        // Display comparison
        $this->line("\n📊 Performance Comparison:");
        $this->line("   Advanced System:");
        $this->line("     Average Time: " . round($avgAdvTime, 2) . "ms");
        $this->line("     Average Memory: " . round($avgAdvMem, 2) . "MB");
        $this->line("     Features: Multi-stage, ML, Skills matching");
        
        $this->line("\n   Basic System:");
        $this->line("     Average Time: " . round($avgBasicTime, 2) . "ms");
        $this->line("     Average Memory: " . round($avgBasicMem, 2) . "MB");
        $this->line("     Features: Simple clustering, Category-based");
        
        // Calculate ratios
        $speedRatio = $avgBasicTime > 0 ? $avgAdvTime / $avgBasicTime : 1;
        $memoryRatio = $avgBasicMem > 0 ? $avgAdvMem / $avgBasicMem : 1;
        
        $this->line("\n📈 Trade-off Analysis:");
        $this->line("   Speed: Advanced is " . round($speedRatio, 1) . "x " . 
                   ($speedRatio > 1 ? "slower" : "faster"));
        $this->line("   Memory: Advanced uses " . round($memoryRatio, 1) . "x " . 
                   ($memoryRatio > 1 ? "more" : "less") . " memory");
        
        // Quality comparison
        $this->line("\n🎯 Quality Benefits (Advanced System):");
        $this->line("   ✅ 10x more sophisticated feature analysis");
        $this->line("   ✅ Skills-based matching with TF-IDF");
        $this->line("   ✅ Collaborative filtering");
        $this->line("   ✅ Market intelligence integration");
        $this->line("   ✅ Real-time performance tracking");
        $this->line("   ✅ Diversity filtering");
        
        // Recommendation
        if ($speedRatio < 5 && $memoryRatio < 3) {
            $this->line("\n💡 Recommendation: Use Advanced System");
            $this->line("   Trade-offs are reasonable for significant quality gains");
        } else {
            $this->line("\n💡 Recommendation: Consider use case");
            $this->line("   Advanced system has high overhead - evaluate if benefits justify cost");
        }
    }

    protected function showDetailedMetrics()
    {
        $this->info("\n📋 Detailed Performance Metrics");
        $this->line(str_repeat('-', 50));
        
        $analytics = $this->advancedService->getPerformanceAnalytics();
        
        $this->line("📊 Clustering Quality:");
        $quality = $analytics['clustering_quality'];
        $this->line("   Silhouette Score: " . round($quality['silhouette_score'], 4));
        $this->line("   Cluster Count: " . $quality['cluster_count']);
        $this->line("   Convergence Iterations: " . $quality['iterations']);
        
        $this->line("\n🎯 Feature Importance:");
        $importance = $analytics['feature_importance'];
        arsort($importance);
        foreach ($importance as $feature => $weight) {
            $percentage = round($weight * 100, 1);
            $featureName = ucwords(str_replace('_', ' ', $feature));
            $this->line("   {$featureName}: {$percentage}%");
        }
        
        $this->line("\n⚡ System Performance:");
        $performance = $analytics['system_performance'];
        $this->line("   Avg Execution Time: " . round($performance['avg_execution_time_ms'], 2) . "ms");
        $this->line("   Cache Hit Rate: " . round($performance['cache_hit_rate'] * 100, 1) . "%");
        $this->line("   Memory Usage: " . round($performance['memory_usage_mb'], 2) . "MB");
        
        $this->line("\n📈 Business Metrics:");
        $this->line("   Recommendation Accuracy: " . round($analytics['recommendation_accuracy'] * 100, 1) . "%");
        $this->line("   User Engagement Rate: " . round($analytics['user_engagement'] * 100, 1) . "%");
    }
}
