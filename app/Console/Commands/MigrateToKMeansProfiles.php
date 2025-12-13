<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Jobseeker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateToKMeansProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kmeans:migrate-profiles 
                          {--dry-run : Show what would be migrated without making changes}
                          {--limit=100 : Limit the number of users to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing jobseekers to the new K-means enhanced profile system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('Starting K-means profile migration...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get jobseekers without proper K-means profile data
        $users = User::where('role', 'jobseeker')
            ->whereDoesntHave('jobseekerProfile', function($query) {
                $query->where('profile_completion_percentage', '>', 0)
                      ->whereNotNull('preferred_categories')
                      ->whereNotNull('skills');
            })
            ->limit($limit)
            ->get();

        $this->info("Found {$users->count()} users to migrate");

        if ($users->isEmpty()) {
            $this->info('No users need migration.');
            return 0;
        }

        $migrated = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                if ($isDryRun) {
                    $this->line("Would migrate: {$user->name} ({$user->email})");
                    $migrated++;
                    continue;
                }

                $this->migrateUser($user);
                $migrated++;
                $this->line("✓ Migrated: {$user->name} ({$user->email})");
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Failed to migrate {$user->name}: " . $e->getMessage());
                Log::error('K-means profile migration failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("\nMigration completed:");
        $this->info("  Migrated: {$migrated}");
        $this->info("  Errors: {$errors}");

        if (!$isDryRun && $migrated > 0) {
            $this->info("\nUsers can now access their K-means enhanced profiles at /kmeans/profile");
        }

        return 0;
    }

    /**
     * Migrate a single user to the K-means profile system
     */
    private function migrateUser(User $user)
    {
        DB::beginTransaction();

        try {
            // Get or create jobseeker profile
            $profile = $user->jobseekerProfile;
            
            if (!$profile) {
                $profile = new Jobseeker();
                $profile->user_id = $user->id;
            }

            // Migrate basic information from user table
            $profile->first_name = $profile->first_name ?: $this->extractFirstName($user->name);
            $profile->last_name = $profile->last_name ?: $this->extractLastName($user->name);
            $profile->phone = $profile->phone ?: $user->mobile;

            // Migrate professional information
            $profile->professional_summary = $profile->professional_summary ?: $user->bio;
            $profile->current_job_title = $profile->current_job_title ?: $user->designation;
            $profile->total_experience_years = $profile->total_experience_years ?: ($user->experience_years ?? 0);

            // Migrate skills with proper formatting for K-means
            if (!$profile->skills && $user->skills) {
                $skills = is_array($user->skills) ? $user->skills : $this->parseSkills($user->skills);
                $profile->skills = array_values(array_unique(array_filter($skills)));
            }

            // Migrate job preferences (critical for K-means)
            if (!$profile->preferred_categories && $user->preferred_categories) {
                $profile->preferred_categories = is_array($user->preferred_categories) 
                    ? $user->preferred_categories 
                    : (json_decode($user->preferred_categories, true) ?: []);
            }

            if (!$profile->preferred_job_types && $user->preferred_job_types) {
                $profile->preferred_job_types = is_array($user->preferred_job_types)
                    ? $user->preferred_job_types
                    : (json_decode($user->preferred_job_types, true) ?: []);
            }

            // Set salary expectations from user data
            if (!$profile->expected_salary_min && $user->salary_expectation_min) {
                $profile->expected_salary_min = $user->salary_expectation_min;
            }
            if (!$profile->expected_salary_max && $user->salary_expectation_max) {
                $profile->expected_salary_max = $user->salary_expectation_max;
            }

            // Set defaults for K-means critical fields
            $profile->salary_currency = $profile->salary_currency ?: 'PHP';
            $profile->salary_period = $profile->salary_period ?: 'monthly';
            $profile->country = $profile->country ?: 'Philippines';
            $profile->availability = $profile->availability ?: 'immediate';

            // Migrate education if available
            if (!$profile->education && $user->education) {
                $education = is_array($user->education) ? $user->education : [$this->parseEducation($user->education)];
                $profile->education = $education;
            }

            // Calculate initial profile completion and search score
            $profile->profile_completion_percentage = $this->calculateProfileCompletion($profile);
            $profile->search_score = $this->calculateSearchScore($profile);
            $profile->profile_status = $this->determineProfileStatus($profile->profile_completion_percentage);

            $profile->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Extract first name from full name
     */
    private function extractFirstName(?string $fullName): ?string
    {
        if (!$fullName) return null;
        return explode(' ', trim($fullName))[0];
    }

    /**
     * Extract last name from full name
     */
    private function extractLastName(?string $fullName): ?string
    {
        if (!$fullName) return null;
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;
    }

    /**
     * Parse skills from string format
     */
    private function parseSkills($skills): array
    {
        if (is_array($skills)) {
            return $skills;
        }

        if (is_string($skills)) {
            // Handle comma-separated skills
            return array_map('trim', explode(',', $skills));
        }

        return [];
    }

    /**
     * Parse education from various formats
     */
    private function parseEducation($education): array
    {
        if (is_array($education)) {
            return $education;
        }

        // Create basic education entry from string
        return [
            'degree' => is_string($education) ? $education : 'Education completed',
            'institution' => '',
            'year' => ''
        ];
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion(Jobseeker $profile): float
    {
        $fields = [
            'preferred_categories' => 15,
            'preferred_job_types' => 15,
            'skills' => 20,
            'expected_salary_min' => 10,
            'city' => 10,
            'professional_summary' => 8,
            'total_experience_years' => 7,
            'education' => 5,
            'first_name' => 3,
            'last_name' => 3,
            'phone' => 2,
            'availability' => 2
        ];

        $totalScore = 0;
        $maxScore = array_sum($fields);

        foreach ($fields as $field => $points) {
            $value = $profile->$field;
            
            if (is_array($value) && !empty($value)) {
                $totalScore += $points;
            } elseif (is_string($value) && !empty(trim($value))) {
                $totalScore += $points;
            } elseif (is_numeric($value) && $value > 0) {
                $totalScore += $points;
            }
        }

        return round(($totalScore / $maxScore) * 100, 2);
    }

    /**
     * Calculate search score for K-means matching
     */
    private function calculateSearchScore(Jobseeker $profile): float
    {
        $score = 0;

        // Base score from profile completion
        $score += $profile->profile_completion_percentage * 0.3;

        // Skills diversity bonus
        if (is_array($profile->skills) && count($profile->skills) >= 3) {
            $score += 10;
        }

        // Experience bonus
        $score += min($profile->total_experience_years * 2, 20);

        // Preference specificity bonus
        if (is_array($profile->preferred_categories) && count($profile->preferred_categories) <= 3) {
            $score += 10;
        }

        return round(min($score, 100), 2);
    }

    /**
     * Determine profile status based on completion
     */
    private function determineProfileStatus(float $completionPercentage): string
    {
        if ($completionPercentage >= 90) {
            return 'complete';
        } elseif ($completionPercentage >= 60) {
            return 'incomplete';
        } else {
            return 'incomplete';
        }
    }
}
