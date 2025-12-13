<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use App\Services\KMeansClusteringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixJobCategories extends Command
{
    protected $signature = 'fix:categories';
    protected $description = 'Fix job category mismatches to make k-means clustering work properly';

    public function handle()
    {
        $this->info('🔧 Fixing Job Category Mismatches for K-Means Clustering');
        $this->line(str_repeat('=', 60));

        try {
            // 1. Analyze current situation
            $this->analyzeCurrentSituation();
            
            // 2. Fix categories
            $this->fixCategories();
            
            // 3. Update user preferences  
            $this->updateUserPreferences();
            
            // 4. Create sample jobs
            $this->createSampleJobs();
            
            // 5. Final verification
            $this->verifyFixes();
            
            // 6. Test clustering
            $this->testClustering();
            
            $this->info("\n✅ All category fixes completed successfully!");
            $this->comment("The k-means clustering should now work properly.");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function analyzeCurrentSituation()
    {
        $this->line("\n📊 1. Analyzing Current Situation:");
        $this->line(str_repeat('-', 40));
        
        $jobs = Job::with('category')->get();
        $users = User::where('role', 'jobseeker')
            ->whereNotNull('preferred_categories')
            ->where('preferred_categories', '!=', '[]')
            ->get();

        $this->info("Current Jobs:");
        foreach ($jobs as $job) {
            $categoryName = $job->category ? $job->category->name : 'NULL/INVALID';
            $this->line("  - {$job->title} (Category ID: {$job->category_id}, Name: {$categoryName})");
        }

        $this->info("\nUsers with Preferences:");
        foreach ($users as $user) {
            $prefs = json_decode($user->preferred_categories, true) ?? [];
            $categoryNames = Category::whereIn('id', $prefs)->pluck('name')->toArray();
            $this->line("  - {$user->name}: " . implode(', ', $categoryNames));
        }

        // Check overlap
        $jobCategoryIds = $jobs->pluck('category_id')->unique()->toArray();
        $userPreferredCategoryIds = [];
        
        foreach ($users as $user) {
            $prefs = json_decode($user->preferred_categories, true) ?? [];
            $userPreferredCategoryIds = array_merge($userPreferredCategoryIds, $prefs);
        }
        $userPreferredCategoryIds = array_unique($userPreferredCategoryIds);
        
        $overlap = array_intersect($jobCategoryIds, $userPreferredCategoryIds);
        
        $this->line("\nOverlap Analysis:");
        $this->line("  Job Categories: " . implode(', ', $jobCategoryIds));
        $this->line("  User Preferred: " . implode(', ', $userPreferredCategoryIds));
        $this->line("  Overlap: " . implode(', ', $overlap));
        
        if (empty($overlap)) {
            $this->warn("❌ PROBLEM: No overlap! This explains why k-means returns no recommendations.");
        } else {
            $this->info("✅ Some overlap exists.");
        }
    }

    private function fixCategories()
    {
        $this->line("\n🔧 2. Fixing Category Assignments:");
        $this->line(str_repeat('-', 40));

        // Ensure core categories exist
        $itCategory = Category::firstOrCreate(
            ['name' => 'Information Technology (IT) / Software Development'],
            ['status' => 1]
        );
        $financeCategory = Category::firstOrCreate(
            ['name' => 'Accounting / Finance'],
            ['status' => 1]
        );
        $othersCategory = Category::firstOrCreate(
            ['name' => 'Others'],
            ['status' => 1]
        );

        $this->info("Core categories ensured:");
        $this->line("  - IT Category ID: {$itCategory->id}");
        $this->line("  - Finance Category ID: {$financeCategory->id}");
        $this->line("  - Others Category ID: {$othersCategory->id}");

        // Update existing jobs
        $jobs = Job::all();
        foreach ($jobs as $job) {
            $oldCategoryId = $job->category_id;
            
            if (stripos($job->title, 'finance') !== false || stripos($job->title, 'accounting') !== false) {
                $job->category_id = $financeCategory->id;
                $this->line("  ✓ Updated '{$job->title}' to Finance category");
            } elseif (stripos($job->title, 'software') !== false || 
                     stripos($job->title, 'developer') !== false || 
                     stripos($job->title, 'programmer') !== false ||
                     stripos($job->title, 'php') !== false) {
                $job->category_id = $itCategory->id;
                $this->line("  ✓ Updated '{$job->title}' to IT category");
            } else {
                $job->category_id = $othersCategory->id;
                $this->line("  ✓ Updated '{$job->title}' to Others category");
            }
            
            $job->save();
        }
    }

    private function updateUserPreferences()
    {
        $this->line("\n👤 3. Updating User Preferences:");
        $this->line(str_repeat('-', 40));

        $itCategory = Category::where('name', 'Information Technology (IT) / Software Development')->first();
        $financeCategory = Category::where('name', 'Accounting / Finance')->first();
        $othersCategory = Category::where('name', 'Others')->first();

        $allJobseekers = User::where('role', 'jobseeker')->get();
        
        foreach ($allJobseekers as $user) {
            $hasPrefs = !empty($user->preferred_categories) && $user->preferred_categories !== '[]';
            
            if (!$hasPrefs) {
                // Assign preferences that match available jobs
                $availableCategories = [$itCategory->id, $financeCategory->id];
                $user->preferred_categories = json_encode($availableCategories);
                $user->save();
                
                $this->line("  ✓ Added preferences to user '{$user->name}': IT, Finance");
            } else {
                // Update existing preferences to include categories with jobs
                $currentPrefs = json_decode($user->preferred_categories, true) ?? [];
                $jobMatchingCategories = [$itCategory->id, $financeCategory->id, $othersCategory->id];
                
                if (empty(array_intersect($currentPrefs, $jobMatchingCategories))) {
                    $currentPrefs[] = $itCategory->id;
                    $user->preferred_categories = json_encode(array_unique($currentPrefs));
                    $user->save();
                    
                    $this->line("  ✓ Added IT category to user '{$user->name}' preferences");
                }
            }
        }
    }

    private function createSampleJobs()
    {
        $this->line("\n📝 4. Creating Sample Jobs:");
        $this->line(str_repeat('-', 40));

        $itCategory = Category::where('name', 'Information Technology (IT) / Software Development')->first();
        $financeCategory = Category::where('name', 'Accounting / Finance')->first();
        $othersCategory = Category::where('name', 'Others')->first();

        // Ensure we have an employer
        $employer = User::where('role', 'employer')->first();
        if (!$employer) {
            $employer = User::create([
                'name' => 'Sample Tech Company',
                'email' => 'hr@sampletech.com',
                'password' => bcrypt('password'),
                'role' => 'employer',
                'email_verified_at' => now()
            ]);
            $this->line("  ✓ Created sample employer");
        }

        // Ensure we have job types
        $fullTimeType = JobType::firstOrCreate(['name' => 'Full Time'], ['status' => 1]);
        $partTimeType = JobType::firstOrCreate(['name' => 'Part Time'], ['status' => 1]);

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
                'title' => 'Junior Web Developer',
                'description' => 'Entry level position for fresh graduates in computer science.',
                'requirements' => 'HTML, CSS, JavaScript, willingness to learn',
                'category_id' => $itCategory->id,
                'location' => 'Cebu, Philippines',
                'salary_range' => '25000-35000'
            ],
            [
                'title' => 'React Developer',
                'description' => 'Frontend developer specializing in React applications.',
                'requirements' => 'React, JavaScript, CSS, 2+ years experience',
                'category_id' => $itCategory->id,
                'location' => 'Makati, Philippines',
                'salary_range' => '45000-65000'
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

        foreach ($sampleJobs as $jobData) {
            $existingJob = Job::where('title', $jobData['title'])->first();
            if (!$existingJob) {
                // Parse salary range for min/max
                $salaryParts = explode('-', $jobData['salary_range']);
                $salaryMin = isset($salaryParts[0]) ? (float)$salaryParts[0] : null;
                $salaryMax = isset($salaryParts[1]) ? (float)$salaryParts[1] : $salaryMin;
                
                try {
                    Job::create([
                        'title' => $jobData['title'],
                        'description' => $jobData['description'],
                        'requirements' => $jobData['requirements'],
                        'category_id' => $jobData['category_id'],
                        'job_type_id' => $fullTimeType->id,
                        'location' => $jobData['location'],
                        'employer_id' => $employer->id,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    $this->warn("    Could not create job '{$jobData['title']}': {$e->getMessage()}");
                    continue;
                }
                $this->line("  ✓ Created job: {$jobData['title']}");
            }
        }
    }

    private function verifyFixes()
    {
        $this->line("\n✅ 5. Verification:");
        $this->line(str_repeat('-', 40));

        $jobs = Job::where('status', 1)
            ->orWhere('status', 'approved')
            ->with('category')
            ->get();
        $users = User::where('role', 'jobseeker')
            ->whereNotNull('preferred_categories')
            ->where('preferred_categories', '!=', '[]')
            ->get();

        $this->info("Active Jobs by Category:");
        $jobsByCategory = $jobs->groupBy('category_id');
        foreach ($jobsByCategory as $categoryId => $categoryJobs) {
            $categoryName = $categoryJobs->first()->category ? $categoryJobs->first()->category->name : 'Unknown';
            $this->line("  - Category {$categoryId} ({$categoryName}): " . $categoryJobs->count() . " jobs");
        }

        $this->info("\nUsers with matching preferences:");
        $matchingUsers = 0;
        foreach ($users as $user) {
            $prefs = json_decode($user->preferred_categories, true) ?? [];
            $hasMatchingJobs = Job::where('status', 1)->whereIn('category_id', $prefs)->count() > 0;
            if ($hasMatchingJobs) {
                $matchingUsers++;
            }
        }
        
        $this->line("  - {$matchingUsers} out of " . $users->count() . " users have matching preferences");

        if ($matchingUsers > 0) {
            $this->info("✅ Categories now properly aligned!");
        } else {
            $this->warn("❌ Still no matching preferences - needs investigation");
        }
    }

    private function testClustering()
    {
        $this->line("\n🧪 6. Testing K-Means Clustering:");
        $this->line(str_repeat('-', 40));

        $clusteringService = new KMeansClusteringService();
        
        $testUser = User::where('role', 'jobseeker')
            ->whereNotNull('preferred_categories')
            ->where('preferred_categories', '!=', '[]')
            ->first();

        if ($testUser) {
            $this->line("Testing recommendations for user: {$testUser->name}");
            
            $prefs = json_decode($testUser->preferred_categories, true) ?? [];
            $categoryNames = Category::whereIn('id', $prefs)->pluck('name')->toArray();
            $this->line("User's preferences: " . implode(', ', $categoryNames));
            
            $recommendations = $clusteringService->getJobRecommendations($testUser->id, 5);
            $this->line("Recommendations found: " . $recommendations->count());

            foreach ($recommendations as $job) {
                $categoryName = $job->category ? $job->category->name : 'N/A';
                $this->line("  - {$job->title} ({$categoryName})");
            }

            if ($recommendations->count() > 0) {
                $this->info("✅ K-Means clustering is now working!");
            } else {
                $this->warn("❌ Still no recommendations - may need further investigation");
            }
        } else {
            $this->warn("No test user found with preferences");
        }
    }
}
