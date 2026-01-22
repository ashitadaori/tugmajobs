<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "  REAL CLUSTERING EXAMPLE: Your System Right Now\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Get actual jobs
$jobs = \App\Models\Job::where('status', 1)->take(10)->get();

echo "📊 STEP 1: JOBS IN YOUR DATABASE\n";
echo "────────────────────────────────────────────────────────────\n";

$service = app(\App\Services\AzureMLClusteringService::class);
$reflection = new ReflectionClass($service);
$extractMethod = $reflection->getMethod('extractJobFeatures');
$extractMethod->setAccessible(true);

foreach ($jobs as $i => $job) {
    $features = $extractMethod->invoke($service, $job);

    echo ($i + 1) . ". {$job->title}\n";
    echo "   Category ID: " . $features['category_id'] . "\n";
    echo "   Salary: ₱" . number_format($features['salary_normalized']) . "\n";
    echo "   Experience: " . $features['experience_level'] . " years\n";
    echo "   Skills Score: " . $features['skills_score'] . "\n";
    echo "   Remote: " . ($features['is_remote'] ? 'Yes' : 'No') . "\n";
    echo "   Days Old: " . $features['days_since_posted'] . " days\n";
    echo "\n";
}

echo "\n🔬 STEP 2: RUNNING CLUSTERING\n";
echo "────────────────────────────────────────────────────────────\n";

$localService = new \App\Services\BasicKMeansClusteringService(
    min(3, $jobs->count()),
    20
);

$startTime = microtime(true);
$clusterResult = $localService->runJobClustering();
$duration = round((microtime(true) - $startTime) * 1000, 2);

if (!empty($clusterResult['clusters'])) {
    echo "✓ Clustering completed in {$duration}ms\n";
    echo "✓ Created " . count($clusterResult['clusters']) . " clusters\n\n";

    echo "📦 STEP 3: CLUSTER RESULTS\n";
    echo "────────────────────────────────────────────────────────────\n\n";

    foreach ($clusterResult['clusters'] as $clusterId => $cluster) {
        echo "CLUSTER {$clusterId}: " . count($cluster) . " jobs\n";
        echo "─────────────────────────────────────\n";

        foreach ($cluster as $item) {
            $jobIndex = $item['index'];
            if (isset($jobs[$jobIndex])) {
                $job = $jobs[$jobIndex];
                echo "  • {$job->title}\n";
            }
        }
        echo "\n";
    }
}

// Test with a real user
echo "\n🎯 STEP 4: RECOMMENDATIONS FOR REAL USER\n";
echo "────────────────────────────────────────────────────────────\n";

$testUser = \App\Models\User::where('role', 'jobseeker')
    ->whereHas('jobSeekerProfile')
    ->first();

if ($testUser) {
    $profile = $testUser->jobSeekerProfile;

    echo "User: {$testUser->name}\n";
    echo "Experience: " . ($profile->total_experience_years ?? 0) . " years\n";
    echo "Preferred Categories: " . json_encode($profile->preferred_categories ?? []) . "\n";
    echo "Expected Salary: ₱" . number_format($profile->expected_salary_min ?? 0) . " - ₱" . number_format($profile->expected_salary_max ?? 0) . "\n";
    echo "\n";

    $recommendations = $service->getJobRecommendations($testUser->id, 5);

    echo "✓ Generated {$recommendations->count()} recommendations:\n\n";

    foreach ($recommendations as $i => $job) {
        echo ($i + 1) . ". {$job->title}\n";
        if ($job->employer && $job->employer->employerProfile) {
            echo "   Company: " . ($job->employer->employerProfile->company_name ?? 'N/A') . "\n";
        }
        echo "   Location: " . ($job->location ?? 'N/A') . "\n";
        echo "   Salary: " . ($job->salary_range ?? 'N/A') . "\n";
        if (isset($job->cluster_score)) {
            echo "   Match Score: " . round($job->cluster_score * 100) . "%\n";
        }
        echo "\n";
    }
} else {
    echo "⚠ No job seeker with profile found\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "  This is exactly how your system clusters jobs!\n";
echo "════════════════════════════════════════════════════════════\n\n";
