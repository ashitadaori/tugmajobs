<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
echo "║   K-MEANS CLUSTERING VERIFICATION REPORT          ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Check mode
$mode = empty(config('azure-ml.endpoint_url')) ? 'LOCAL' : 'AZURE ML';
$modeColor = $mode === 'LOCAL' ? '🟢' : '🔵';

echo "{$modeColor} MODE: {$mode} CLUSTERING\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Database stats
echo "📊 DATA AVAILABILITY\n";
echo "────────────────────────────────────────────────────\n";
$totalJobs = \App\Models\Job::count();
$activeJobs = \App\Models\Job::where('status', 1)->count();
$totalUsers = \App\Models\User::where('role', 'jobseeker')->count();
$usersWithProfile = \App\Models\User::where('role', 'jobseeker')
    ->whereHas('jobSeekerProfile')
    ->count();

echo "Jobs (Total):        {$totalJobs}\n";
echo "Jobs (Active):       {$activeJobs} " . ($activeJobs > 0 ? "✓" : "✗ Need active jobs") . "\n";
echo "Job Seekers:         {$totalUsers}\n";
echo "With Profiles:       {$usersWithProfile} " . ($usersWithProfile > 0 ? "✓" : "⚠ Profiles needed") . "\n";
echo "\n";

// Test job recommendations
echo "🎯 JOB RECOMMENDATIONS TEST\n";
echo "────────────────────────────────────────────────────\n";

$testUser = \App\Models\User::where('role', 'jobseeker')
    ->whereHas('jobSeekerProfile')
    ->first();

if ($testUser) {
    try {
        $service = app(\App\Services\AzureMLClusteringService::class);
        $startTime = microtime(true);
        $recommendations = $service->getJobRecommendations($testUser->id, 10);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        echo "Test User:           ID {$testUser->id} ({$testUser->name})\n";
        echo "Recommendations:     {$recommendations->count()} jobs found\n";
        echo "Processing Time:     {$duration}ms\n";
        echo "Status:              " . ($recommendations->count() > 0 ? "✓ WORKING" : "⚠ No matches") . "\n";

        if ($recommendations->count() > 0) {
            echo "\n📋 Sample Recommendations:\n";
            foreach ($recommendations->take(3) as $i => $job) {
                $score = isset($job->cluster_score) ? round($job->cluster_score, 3) : 'N/A';
                echo "   " . ($i + 1) . ". {$job->title} (Score: {$score})\n";
            }
        }
    } catch (Exception $e) {
        echo "Status:              ✗ FAILED\n";
        echo "Error:               {$e->getMessage()}\n";
    }
} else {
    echo "Status:              ⚠ No test user available\n";
    echo "Action:              Create job seeker with profile to test\n";
}

echo "\n";

// Test actual clustering
echo "🔬 CLUSTERING ENGINE TEST\n";
echo "────────────────────────────────────────────────────\n";

if ($activeJobs >= 5) {
    try {
        $service = app(\App\Services\AzureMLClusteringService::class);

        // Try job clustering directly
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getJobTrainingData');
        $method->setAccessible(true);
        $trainingData = $method->invoke($service);

        echo "Training Data:       " . count($trainingData) . " jobs extracted\n";

        if (count($trainingData) > 0) {
            // Show sample features
            $sampleFeatures = $trainingData[0];
            echo "Feature Count:       " . count($sampleFeatures) . " features per job\n";
            echo "Sample Features:     " . implode(', ', array_keys($sampleFeatures)) . "\n";

            // Test local clustering
            $localService = new \App\Services\BasicKMeansClusteringService(
                min(3, count($trainingData)),
                20
            );

            $startTime = microtime(true);
            $clusterResult = $localService->runJobClustering();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if (!empty($clusterResult['clusters']) && !empty($clusterResult['centroids'])) {
                $numClusters = count($clusterResult['centroids']);
                echo "Clusters Created:    {$numClusters}\n";
                echo "Processing Time:     {$duration}ms\n";
                echo "Status:              ✓ CLUSTERING WORKS\n";

                // Show cluster distribution
                echo "\n📊 Cluster Distribution:\n";
                foreach ($clusterResult['clusters'] as $i => $cluster) {
                    $size = count($cluster);
                    $bar = str_repeat('█', min(50, $size));
                    echo "   Cluster {$i}: {$size} jobs {$bar}\n";
                }
            } else {
                echo "Status:              ✗ CLUSTERING FAILED\n";
            }
        }
    } catch (Exception $e) {
        echo "Status:              ✗ ERROR\n";
        echo "Error:               {$e->getMessage()}\n";
    }
} else {
    echo "Status:              ⚠ INSUFFICIENT DATA\n";
    echo "Required:            At least 5 active jobs\n";
    echo "Current:             {$activeJobs} active jobs\n";
}

echo "\n";

// Cache test
echo "⚡ CACHE PERFORMANCE TEST\n";
echo "────────────────────────────────────────────────────\n";

if (config('azure-ml.cache.enabled')) {
    try {
        $cacheKey = 'test_cache_' . time();

        // Write test
        \Illuminate\Support\Facades\Cache::put($cacheKey, ['test' => 'data'], 60);
        $retrieved = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($retrieved && isset($retrieved['test'])) {
            echo "Cache Write:         ✓ Working\n";
            echo "Cache Read:          ✓ Working\n";
            echo "Cache TTL:           " . config('azure-ml.cache.ttl') . " seconds (" . round(config('azure-ml.cache.ttl')/3600, 1) . " hours)\n";
            echo "Status:              ✓ CACHE OPERATIONAL\n";
        }

        \Illuminate\Support\Facades\Cache::forget($cacheKey);
    } catch (Exception $e) {
        echo "Status:              ✗ CACHE ERROR\n";
        echo "Error:               {$e->getMessage()}\n";
    }
} else {
    echo "Status:              ⚠ CACHE DISABLED\n";
}

echo "\n";

// System health
echo "💚 SYSTEM HEALTH CHECK\n";
echo "────────────────────────────────────────────────────\n";

$healthScore = 0;
$maxScore = 5;

// Check 1: Database
if ($activeJobs > 0 && $totalUsers > 0) {
    echo "✓ Database connectivity\n";
    $healthScore++;
} else {
    echo "✗ Database needs data\n";
}

// Check 2: Clustering service
try {
    $service = app(\App\Services\AzureMLClusteringService::class);
    echo "✓ Clustering service loaded\n";
    $healthScore++;
} catch (Exception $e) {
    echo "✗ Clustering service error\n";
}

// Check 3: Fallback
if (config('azure-ml.fallback.enabled')) {
    echo "✓ Fallback mechanism enabled\n";
    $healthScore++;
} else {
    echo "⚠ Fallback disabled (risky)\n";
}

// Check 4: Configuration
if (!empty(config('azure-ml.clustering.default_k'))) {
    echo "✓ Configuration loaded\n";
    $healthScore++;
} else {
    echo "✗ Configuration missing\n";
}

// Check 5: Recommendations working
if (isset($recommendations) && $recommendations->count() > 0) {
    echo "✓ Recommendations functional\n";
    $healthScore++;
} else {
    echo "⚠ Recommendations need more data\n";
}

$healthPercent = round(($healthScore / $maxScore) * 100);
$healthBar = str_repeat('█', $healthScore) . str_repeat('░', $maxScore - $healthScore);

echo "\n";
echo "Health Score:        [{$healthBar}] {$healthScore}/{$maxScore} ({$healthPercent}%)\n";

// Final verdict
echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";

if ($healthScore >= 4) {
    echo "║          ✅ CLUSTERING SYSTEM OPERATIONAL          ║\n";
} elseif ($healthScore >= 3) {
    echo "║          ⚠️  CLUSTERING WORKING WITH WARNINGS      ║\n";
} else {
    echo "║          ❌ CLUSTERING NEEDS ATTENTION             ║\n";
}

echo "╚════════════════════════════════════════════════════╝\n";

echo "\n";
echo "📝 SUMMARY\n";
echo "────────────────────────────────────────────────────\n";
echo "Mode:                {$mode}\n";
echo "Cost:                " . ($mode === 'LOCAL' ? '$0/month' : '~$102/month') . "\n";
echo "Performance:         " . ($mode === 'LOCAL' ? 'Good' : 'Excellent') . "\n";
echo "Recommendations:     " . (isset($recommendations) ? $recommendations->count() . ' generated' : 'N/A') . "\n";

if ($mode === 'LOCAL') {
    echo "Next Step:           System ready! Add more jobs/users for better results\n";
} else {
    echo "Next Step:           Monitor Azure costs and performance\n";
}

echo "\n";
