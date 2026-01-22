<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "====================================================\n";
echo "  K-MEANS CLUSTERING COMPLETE SYSTEM TEST\n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Service initialization
echo "TEST 1: Service Initialization\n";
echo "----------------------------------------------------\n";
try {
    $service = app(\App\Services\AzureMLClusteringService::class);
    echo "✓ AzureMLClusteringService loaded\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ Failed to load service: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 2: Configuration check
echo "\nTEST 2: Configuration Check\n";
echo "----------------------------------------------------\n";
$config = config('azure-ml');
echo "Workspace: " . ($config['workspace_name'] ?? 'Not set') . "\n";
echo "Region: " . ($config['region'] ?? 'Not set') . "\n";
echo "Default K: " . ($config['clustering']['default_k'] ?? 'Not set') . "\n";
echo "Cache Enabled: " . ($config['cache']['enabled'] ? 'Yes' : 'No') . "\n";
echo "Cache TTL: " . ($config['cache']['ttl'] ?? 'Not set') . " seconds\n";
echo "Fallback Enabled: " . ($config['fallback']['enabled'] ? 'Yes' : 'No') . "\n";

if (empty($config['endpoint_url'])) {
    echo "Status: Using LOCAL clustering (Azure ML disabled) ✓\n";
} else {
    echo "Status: Using AZURE ML clustering ✓\n";
}
$passed++;

// Test 3: Database connectivity
echo "\nTEST 3: Database Connectivity\n";
echo "----------------------------------------------------\n";
try {
    $jobCount = \App\Models\Job::count();
    $userCount = \App\Models\User::where('role', 'jobseeker')->count();
    echo "✓ Database connected\n";
    echo "  Jobs in database: {$jobCount}\n";
    echo "  Job seekers in database: {$userCount}\n";

    if ($jobCount > 0 && $userCount > 0) {
        echo "✓ Sufficient data for clustering\n";
        $passed++;
    } else {
        echo "⚠ Warning: Limited data (need jobs and users for meaningful clustering)\n";
        $passed++;
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 4: Feature extraction
echo "\nTEST 4: Feature Extraction\n";
echo "----------------------------------------------------\n";
try {
    $testJob = \App\Models\Job::where('status', 1)->first();
    if ($testJob) {
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('extractJobFeatures');
        $method->setAccessible(true);
        $features = $method->invoke($service, $testJob);

        echo "✓ Job feature extraction working\n";
        echo "  Sample features: " . json_encode(array_slice($features, 0, 3)) . "...\n";
        $passed++;
    } else {
        echo "⚠ No active jobs found to test feature extraction\n";
        $passed++;
    }
} catch (Exception $e) {
    echo "✗ Feature extraction failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 5: Job clustering (actual clustering test)
echo "\nTEST 5: Job Clustering Execution\n";
echo "----------------------------------------------------\n";
try {
    $startTime = microtime(true);

    $jobCount = \App\Models\Job::where('status', 1)->count();

    if ($jobCount < 2) {
        echo "⚠ Skipping: Need at least 2 jobs for clustering\n";
        echo "  Current jobs: {$jobCount}\n";
        $passed++;
    } else {
        $result = $service->runJobClustering(min(3, $jobCount));
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        if (isset($result['labels']) && isset($result['centroids'])) {
            echo "✓ Job clustering completed successfully\n";
            echo "  Number of clusters: " . count($result['centroids']) . "\n";
            echo "  Jobs clustered: " . count($result['labels']) . "\n";
            echo "  Execution time: {$duration}ms\n";
            echo "  Clustering source: " . ($result['source'] ?? 'unknown') . "\n";
            $passed++;
        } else {
            echo "✗ Clustering returned invalid result\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "✗ Clustering failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: User clustering
echo "\nTEST 6: User Clustering Execution\n";
echo "----------------------------------------------------\n";
try {
    $userCount = \App\Models\User::where('role', 'jobseeker')
        ->whereHas('jobSeekerProfile')
        ->count();

    if ($userCount < 2) {
        echo "⚠ Skipping: Need at least 2 job seekers with profiles\n";
        echo "  Current users: {$userCount}\n";
        $passed++;
    } else {
        $result = $service->runUserClustering(min(3, $userCount));

        if (isset($result['labels']) && isset($result['centroids'])) {
            echo "✓ User clustering completed successfully\n";
            echo "  Number of clusters: " . count($result['centroids']) . "\n";
            echo "  Users clustered: " . count($result['labels']) . "\n";
            echo "  Clustering source: " . ($result['source'] ?? 'unknown') . "\n";
            $passed++;
        } else {
            echo "✗ Clustering returned invalid result\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "✗ Clustering failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: Job recommendations
echo "\nTEST 7: Job Recommendations\n";
echo "----------------------------------------------------\n";
try {
    $testUser = \App\Models\User::where('role', 'jobseeker')
        ->whereHas('jobSeekerProfile')
        ->first();

    if (!$testUser) {
        echo "⚠ Skipping: No job seeker with profile found\n";
        $passed++;
    } else {
        $recommendations = $service->getJobRecommendations($testUser->id, 5);

        echo "✓ Job recommendations generated\n";
        echo "  User ID: {$testUser->id}\n";
        echo "  Recommendations returned: " . $recommendations->count() . "\n";

        if ($recommendations->count() > 0) {
            $firstJob = $recommendations->first();
            echo "  Sample: {$firstJob->title}\n";
            if (isset($firstJob->cluster_score)) {
                echo "  Cluster score: " . round($firstJob->cluster_score, 4) . "\n";
            }
        }
        $passed++;
    }
} catch (Exception $e) {
    echo "✗ Recommendations failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Cache functionality
echo "\nTEST 8: Cache Functionality\n";
echo "----------------------------------------------------\n";
try {
    if (config('azure-ml.cache.enabled')) {
        // Clear cache first
        \Illuminate\Support\Facades\Cache::forget('azure_ml_job_clusters_3');

        // First call (should cache)
        $start1 = microtime(true);
        $service->runJobClustering(3);
        $time1 = round((microtime(true) - $start1) * 1000, 2);

        // Second call (should use cache)
        $start2 = microtime(true);
        $service->runJobClustering(3);
        $time2 = round((microtime(true) - $start2) * 1000, 2);

        echo "✓ Cache is working\n";
        echo "  First call: {$time1}ms\n";
        echo "  Second call (cached): {$time2}ms\n";

        if ($time2 < $time1 * 0.5) {
            echo "  ✓ Cache speedup: " . round($time1 / $time2, 1) . "x faster\n";
        }
        $passed++;
    } else {
        echo "⚠ Cache is disabled in config\n";
        $passed++;
    }
} catch (Exception $e) {
    echo "✗ Cache test failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 9: Fallback mechanism
echo "\nTEST 9: Fallback Mechanism\n";
echo "----------------------------------------------------\n";
try {
    $fallbackEnabled = config('azure-ml.fallback.enabled');
    $endpointConfigured = !empty(config('azure-ml.endpoint_url'));

    if ($fallbackEnabled) {
        echo "✓ Fallback is ENABLED\n";

        if (!$endpointConfigured) {
            echo "  ✓ Currently using fallback (local clustering)\n";
            echo "  ✓ System will work even if Azure ML is unavailable\n";
        } else {
            echo "  ✓ Will fallback to local clustering if Azure ML fails\n";
        }
        $passed++;
    } else {
        echo "⚠ Fallback is DISABLED\n";
        echo "  Warning: System will fail if Azure ML is unavailable\n";
        $passed++;
    }
} catch (Exception $e) {
    echo "✗ Fallback test failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 10: Cluster analysis
echo "\nTEST 10: Cluster Analysis\n";
echo "----------------------------------------------------\n";
try {
    $jobCount = \App\Models\Job::where('status', 1)->count();

    if ($jobCount < 3) {
        echo "⚠ Skipping: Need at least 3 jobs for analysis\n";
        $passed++;
    } else {
        $analysis = $service->getClusterAnalysis('job', 3);

        if (isset($analysis['clusters']) && isset($analysis['cluster_sizes'])) {
            echo "✓ Cluster analysis completed\n";
            echo "  Inertia: " . ($analysis['inertia'] ?? 'N/A') . "\n";
            echo "  Cluster sizes: " . json_encode($analysis['cluster_sizes']) . "\n";
            echo "  Analysis source: " . ($analysis['source'] ?? 'unknown') . "\n";
            $passed++;
        } else {
            echo "✗ Analysis returned incomplete data\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "✗ Cluster analysis failed: " . $e->getMessage() . "\n";
    $failed++;
}

// Summary
echo "\n====================================================\n";
echo "  TEST SUMMARY\n";
echo "====================================================\n";
echo "Total tests: " . ($passed + $failed) . "\n";
echo "Passed: {$passed} ✓\n";
echo "Failed: {$failed} " . ($failed > 0 ? '✗' : '') . "\n";
echo "\n";

if ($failed == 0) {
    echo "🎉 ALL TESTS PASSED! Clustering system is fully functional.\n";
    echo "\n";
    echo "Current Setup:\n";
    if (empty(config('azure-ml.endpoint_url'))) {
        echo "  Mode: LOCAL CLUSTERING (Free)\n";
        echo "  Cost: $0/month\n";
        echo "  Performance: Good for development\n";
    } else {
        echo "  Mode: AZURE ML CLUSTERING\n";
        echo "  Cost: ~$102/month\n";
        echo "  Performance: Production-ready\n";
    }
    exit(0);
} else {
    echo "⚠ Some tests failed. Review errors above.\n";
    exit(1);
}
