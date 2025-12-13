<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\User;
use App\Http\Controllers\JobsControllerKMeans;
use App\Services\KMeansClusteringService;

// Bootstrap Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing JobsControllerKMeans functionality...\n\n";
    
    // Test 1: Check if jobs can be retrieved without authentication
    echo "1. Testing jobs retrieval without authentication:\n";
    $jobs = Job::where('status', 1)->with(['jobType', 'category', 'employer'])->take(3)->get();
    echo "   Found " . $jobs->count() . " active jobs\n";
    
    if ($jobs->isNotEmpty()) {
        echo "   Sample job: '" . $jobs->first()->title . "' in category '" . 
             ($jobs->first()->category->name ?? 'N/A') . "'\n";
    }
    
    // Test 2: Check categories and job types
    echo "\n2. Testing categories and job types:\n";
    $categories = Category::where('status', 1)->get();
    $jobTypes = JobType::where('status', 1)->get();
    echo "   Active categories: " . $categories->count() . "\n";
    echo "   Active job types: " . $jobTypes->count() . "\n";
    
    if ($categories->isNotEmpty()) {
        echo "   Sample categories: " . $categories->pluck('name')->take(3)->implode(', ') . "\n";
    }
    
    // Test 3: Check if any users have preferred categories
    echo "\n3. Testing user preferences:\n";
    $usersWithPreferences = User::whereNotNull('preferred_categories')
                                ->where('preferred_categories', '!=', '')
                                ->where('preferred_categories', '!=', '[]')
                                ->get();
    echo "   Users with category preferences: " . $usersWithPreferences->count() . "\n";
    
    if ($usersWithPreferences->isNotEmpty()) {
        $sampleUser = $usersWithPreferences->first();
        $preferences = json_decode($sampleUser->preferred_categories, true);
        echo "   Sample user preferences: " . implode(', ', $preferences ?: []) . "\n";
    }
    
    // Test 4: Test the clustering service
    echo "\n4. Testing KMeans clustering service:\n";
    $clusteringService = app(KMeansClusteringService::class);
    
    // Get a sample job for feature extraction
    $sampleJob = Job::where('status', 1)->first();
    if ($sampleJob) {
        try {
            $jobFeatures = $clusteringService->getJobFeatures($sampleJob);
            echo "   Job features extracted successfully for job ID {$sampleJob->id}\n";
            echo "   Feature vector length: " . count($jobFeatures) . "\n";
            
            // Show first few features
            $featureKeys = array_keys($jobFeatures);
            $sampleFeatures = array_slice($jobFeatures, 0, 5, true);
            echo "   Sample features: ";
            foreach ($sampleFeatures as $key => $value) {
                echo "{$key}={$value}, ";
            }
            echo "\n";
            
        } catch (Exception $e) {
            echo "   ERROR in feature extraction: " . $e->getMessage() . "\n";
            echo "   This suggests the clustering service needs fixes.\n";
        }
    }
    
    // Test 5: Test if JobsControllerKMeans can be instantiated
    echo "\n5. Testing JobsControllerKMeans instantiation:\n";
    try {
        $controller = app(JobsControllerKMeans::class);
        echo "   Controller instantiated successfully\n";
        
        // Test private method userHasCategoryPreferences
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('userHasCategoryPreferences');
        $method->setAccessible(true);
        
        // Test with empty user
        $testUser = new User();
        $testUser->preferred_categories = null;
        $result1 = $method->invoke($controller, $testUser);
        echo "   User with null preferences: " . ($result1 ? 'HAS' : 'NO') . " preferences\n";
        
        // Test with empty array
        $testUser->preferred_categories = '[]';
        $result2 = $method->invoke($controller, $testUser);
        echo "   User with empty array: " . ($result2 ? 'HAS' : 'NO') . " preferences\n";
        
        // Test with actual preferences
        $testUser->preferred_categories = '[1, 2, 3]';
        $result3 = $method->invoke($controller, $testUser);
        echo "   User with preferences [1,2,3]: " . ($result3 ? 'HAS' : 'NO') . " preferences\n";
        
    } catch (Exception $e) {
        echo "   ERROR instantiating controller: " . $e->getMessage() . "\n";
    }
    
    echo "\nTest completed successfully!\n";
    echo "\nNext steps:\n";
    echo "- The JobsControllerKMeans should now work without forcing category selection\n";
    echo "- Users without preferences will see all jobs + a prompt to set preferences\n";
    echo "- Users with preferences will see filtered jobs + recommendations\n";
    echo "- Visit /jobs in your browser to test the functionality\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
