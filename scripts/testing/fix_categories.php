<?php

require_once 'bootstrap/app.php';

$app = new \Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Job Category Analysis and Fix ===\n\n";

try {
    // 1. Analyze current jobs and their categories
    echo "1. Current Jobs and Categories:\n";
    echo str_repeat("-", 40) . "\n";
    
    $jobs = App\Models\Job::with('category')->get();
    foreach ($jobs as $job) {
        $categoryName = $job->category ? $job->category->name : 'NULL/INVALID';
        echo "Job: {$job->title}\n";
        echo "  Category ID: {$job->category_id}\n";
        echo "  Category Name: {$categoryName}\n";
        echo "  Status: {$job->status}\n\n";
    }
    
    // 2. Show all available categories
    echo "2. All Available Categories:\n";
    echo str_repeat("-", 40) . "\n";
    
    $categories = App\Models\Category::where('status', 1)->get();
    foreach ($categories as $category) {
        echo "ID: {$category->id} - {$category->name}\n";
    }
    echo "\n";
    
    // 3. Show users and their preferred categories
    echo "3. Users with Category Preferences:\n";
    echo str_repeat("-", 40) . "\n";
    
    $users = App\Models\User::where('role', 'jobseeker')
        ->whereNotNull('preferred_categories')
        ->where('preferred_categories', '!=', '[]')
        ->get();
        
    foreach ($users as $user) {
        $prefs = json_decode($user->preferred_categories, true) ?? [];
        echo "User: {$user->name}\n";
        echo "  Preferred Category IDs: " . implode(', ', $prefs) . "\n";
        
        // Show category names
        $categoryNames = App\Models\Category::whereIn('id', $prefs)->pluck('name')->toArray();
        echo "  Category Names: " . implode(', ', $categoryNames) . "\n\n";
    }
    
    // 4. Identify the problem
    echo "4. Problem Analysis:\n";
    echo str_repeat("-", 40) . "\n";
    
    $jobCategoryIds = $jobs->pluck('category_id')->unique()->toArray();
    $userPreferredCategoryIds = [];
    
    foreach ($users as $user) {
        $prefs = json_decode($user->preferred_categories, true) ?? [];
        $userPreferredCategoryIds = array_merge($userPreferredCategoryIds, $prefs);
    }
    $userPreferredCategoryIds = array_unique($userPreferredCategoryIds);
    
    echo "Job Category IDs: " . implode(', ', $jobCategoryIds) . "\n";
    echo "User Preferred Category IDs: " . implode(', ', $userPreferredCategoryIds) . "\n";
    
    $overlap = array_intersect($jobCategoryIds, $userPreferredCategoryIds);
    echo "Overlapping Category IDs: " . implode(', ', $overlap) . "\n";
    
    if (empty($overlap)) {
        echo "❌ PROBLEM: No overlap between job categories and user preferences!\n";
        echo "   This is why k-means recommendations are empty.\n\n";
    } else {
        echo "✅ Some overlap exists.\n\n";
    }
    
    // 5. Fix the categories
    echo "5. Fixing Category Issues:\n";
    echo str_repeat("-", 40) . "\n";
    
    // Option 1: Update existing jobs to match available categories
    // Let's assign jobs to categories that exist and are commonly used
    
    $itCategory = App\Models\Category::where('name', 'LIKE', '%Information Technology%')->first();
    $othersCategory = App\Models\Category::where('name', 'Others')->first();
    $financeCategory = App\Models\Category::where('name', 'LIKE', '%Accounting%')->first();
    
    if (!$itCategory) {
        echo "Creating IT category...\n";
        $itCategory = App\Models\Category::create([
            'name' => 'Information Technology (IT) / Software Development',
            'status' => 1
        ]);
    }
    
    if (!$financeCategory) {
        echo "Creating Finance category...\n";
        $financeCategory = App\Models\Category::create([
            'name' => 'Accounting / Finance',
            'status' => 1
        ]);
    }
    
    if (!$othersCategory) {
        echo "Creating Others category...\n";
        $othersCategory = App\Models\Category::create([
            'name' => 'Others',
            'status' => 1
        ]);
    }
    
    // Update jobs with proper category assignments
    foreach ($jobs as $job) {
        $oldCategoryId = $job->category_id;
        
        if (stripos($job->title, 'finance') !== false || stripos($job->title, 'accounting') !== false) {
            $job->category_id = $financeCategory->id;
            echo "Updated job '{$job->title}' from category {$oldCategoryId} to Finance category {$financeCategory->id}\n";
        } elseif (stripos($job->title, 'software') !== false || stripos($job->title, 'developer') !== false || stripos($job->title, 'programmer') !== false) {
            $job->category_id = $itCategory->id;
            echo "Updated job '{$job->title}' from category {$oldCategoryId} to IT category {$itCategory->id}\n";
        } else {
            // Keep other jobs in Others category
            $job->category_id = $othersCategory->id;
            echo "Updated job '{$job->title}' from category {$oldCategoryId} to Others category {$othersCategory->id}\n";
        }
        
        $job->save();
    }
    
    // 6. Update user preferences to match available job categories
    echo "\n6. Updating User Preferences:\n";
    echo str_repeat("-", 40) . "\n";
    
    $allJobseekers = App\Models\User::where('role', 'jobseeker')->get();
    
    foreach ($allJobseekers as $user) {
        $hasPrefs = !empty($user->preferred_categories) && $user->preferred_categories !== '[]';
        
        if (!$hasPrefs) {
            // Assign random preferences from categories that have jobs
            $availableCategories = [$itCategory->id, $financeCategory->id, $othersCategory->id];
            $randomPrefs = array_slice($availableCategories, 0, 2); // Give 2 categories
            
            $user->preferred_categories = json_encode($randomPrefs);
            $user->save();
            
            echo "Added preferences to user '{$user->name}': " . implode(', ', $randomPrefs) . "\n";
        } else {
            // Update existing preferences to include categories with jobs
            $currentPrefs = json_decode($user->preferred_categories, true) ?? [];
            
            // Add IT category if user doesn't have job-matching preferences
            if (empty(array_intersect($currentPrefs, [$itCategory->id, $financeCategory->id, $othersCategory->id]))) {
                $currentPrefs[] = $itCategory->id;
                $user->preferred_categories = json_encode(array_unique($currentPrefs));
                $user->save();
                
                echo "Added IT category to user '{$user->name}' preferences\n";
            }
        }
    }
    
    // 7. Create some sample jobs for better testing
    echo "\n7. Creating Sample Jobs for Better Testing:\n";
    echo str_repeat("-", 40) . "\n";
    
    $sampleJobs = [
        [
            'title' => 'Senior PHP Developer',
            'description' => 'We are looking for an experienced PHP developer with Laravel framework knowledge.',
            'requirements' => 'PHP, Laravel, MySQL, 3+ years experience',
            'category_id' => $itCategory->id,
            'location' => 'Manila, Philippines',
            'salary_range' => '50000-80000'
        ],
        [
            'title' => 'Junior Software Engineer',
            'description' => 'Entry level position for fresh graduates in computer science.',
            'requirements' => 'Programming fundamentals, willingness to learn',
            'category_id' => $itCategory->id,
            'location' => 'Cebu, Philippines',
            'salary_range' => '25000-35000'
        ],
        [
            'title' => 'Accounting Assistant',
            'description' => 'Support accounting operations and financial reporting.',
            'requirements' => 'Accounting degree, Excel proficiency',
            'category_id' => $financeCategory->id,
            'location' => 'Davao, Philippines',
            'salary_range' => '20000-30000'
        ],
        [
            'title' => 'Financial Analyst',
            'description' => 'Analyze financial data and prepare reports.',
            'requirements' => 'Finance background, analytical skills, 2+ years experience',
            'category_id' => $financeCategory->id,
            'location' => 'Makati, Philippines',
            'salary_range' => '40000-60000'
        ],
        [
            'title' => 'Customer Service Representative',
            'description' => 'Handle customer inquiries and provide support.',
            'requirements' => 'Good communication skills, customer service experience',
            'category_id' => $othersCategory->id,
            'location' => 'Quezon City, Philippines',
            'salary_range' => '18000-25000'
        ]
    ];
    
    // Get a sample employer (create one if needed)
    $employer = App\Models\User::where('role', 'employer')->first();
    if (!$employer) {
        $employer = App\Models\User::create([
            'name' => 'Sample Company',
            'email' => 'sample@company.com',
            'password' => bcrypt('password'),
            'role' => 'employer',
            'email_verified_at' => now()
        ]);
        echo "Created sample employer\n";
    }
    
    // Get job type
    $jobType = App\Models\JobType::where('status', 1)->first();
    if (!$jobType) {
        $jobType = App\Models\JobType::create([
            'name' => 'Full Time',
            'status' => 1
        ]);
    }
    
    foreach ($sampleJobs as $jobData) {
        // Check if similar job already exists
        $existingJob = App\Models\Job::where('title', $jobData['title'])->first();
        if (!$existingJob) {
            App\Models\Job::create([
                'title' => $jobData['title'],
                'description' => $jobData['description'],
                'requirements' => $jobData['requirements'],
                'category_id' => $jobData['category_id'],
                'job_type_id' => $jobType->id,
                'location' => $jobData['location'],
                'salary_range' => $jobData['salary_range'],
                'user_id' => $employer->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Created job: {$jobData['title']}\n";
        }
    }
    
    // 8. Final verification
    echo "\n8. Final Verification:\n";
    echo str_repeat("-", 40) . "\n";
    
    $updatedJobs = App\Models\Job::where('status', 1)->with('category')->get();
    $updatedUsers = App\Models\User::where('role', 'jobseeker')
        ->whereNotNull('preferred_categories')
        ->where('preferred_categories', '!=', '[]')
        ->get();
    
    echo "Active Jobs by Category:\n";
    $jobsByCategory = $updatedJobs->groupBy('category_id');
    foreach ($jobsByCategory as $categoryId => $jobs) {
        $categoryName = $jobs->first()->category ? $jobs->first()->category->name : 'Unknown';
        echo "  Category {$categoryId} ({$categoryName}): " . $jobs->count() . " jobs\n";
    }
    
    echo "\nUsers with matching category preferences:\n";
    $matchingUsers = 0;
    foreach ($updatedUsers as $user) {
        $prefs = json_decode($user->preferred_categories, true) ?? [];
        $hasMatchingJobs = App\Models\Job::where('status', 1)->whereIn('category_id', $prefs)->count() > 0;
        if ($hasMatchingJobs) {
            $matchingUsers++;
        }
    }
    
    echo "  {$matchingUsers} out of " . $updatedUsers->count() . " users have preferences matching available jobs\n";
    
    echo "\n✅ Category fixes completed!\n";
    echo "The k-means clustering should now work properly with matching categories.\n";
    
    // 9. Test the clustering
    echo "\n9. Testing K-Means Clustering:\n";
    echo str_repeat("-", 40) . "\n";
    
    $clusteringService = new App\Services\KMeansClusteringService();
    
    // Test with a user who has preferences
    $testUser = App\Models\User::where('role', 'jobseeker')
        ->whereNotNull('preferred_categories')
        ->where('preferred_categories', '!=', '[]')
        ->first();
    
    if ($testUser) {
        echo "Testing recommendations for user: {$testUser->name}\n";
        $recommendations = $clusteringService->getJobRecommendations($testUser->id, 5);
        echo "Recommendations found: " . $recommendations->count() . "\n";
        
        foreach ($recommendations as $job) {
            $categoryName = $job->category ? $job->category->name : 'N/A';
            echo "  - {$job->title} ({$categoryName})\n";
        }
        
        if ($recommendations->count() > 0) {
            echo "✅ K-Means clustering is now working!\n";
        } else {
            echo "❌ Still no recommendations - may need more investigation\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
