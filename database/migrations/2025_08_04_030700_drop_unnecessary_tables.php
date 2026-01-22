<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to drop unnecessary tables.
     */
    public function up(): void
    {
        // Step 1: Migrate data from job_seeker_profiles to jobseekers (if any additional data exists)
        $this->migrateJobSeekerProfilesData();
        
        // Step 2: Migrate application status histories to a single table
        $this->consolidateApplicationStatusHistories();
        
        // Step 3: Drop empty/unused tables first
        // Drop child tables before parent tables to avoid foreign key constraint errors
        $tablesToDrop = [
            // Drop job alert child tables first
            'job_alert_categories',
            'job_alert_job_types', 
            'job_alerts', // Then drop parent
            
            // Other empty tables
            'application_status_history',
            'kyc_documents',
            'kyc_verifications',
            'team_members'
        ];
        
        foreach ($tablesToDrop as $table) {
            if (Schema::hasTable($table)) {
                // Disable foreign key checks temporarily
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                Schema::dropIfExists($table);
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                echo "Dropped table: {$table}\n";
            }
        }
        
        // Step 4: Drop tables with migrated data
        Schema::dropIfExists('job_seeker_profiles');
        
        // Note: Keep application_status_histories as it has useful data
        // We'll rename it to be more consistent
        if (Schema::hasTable('application_status_histories')) {
            Schema::rename('application_status_histories', 'job_application_status_histories');
        }
        
        // Step 5: Clean up any other redundant tables
        $this->cleanupRedundantTables();
    }
    
    /**
     * Migrate any additional data from job_seeker_profiles to jobseekers table
     */
    private function migrateJobSeekerProfilesData(): void
    {
        try {
            $profiles = DB::table('job_seeker_profiles')->get();
            
            foreach ($profiles as $profile) {
                // Check if the user already has a jobseeker profile
                $existingJobseeker = DB::table('jobseekers')
                    ->where('user_id', $profile->user_id)
                    ->first();
                
                if (!$existingJobseeker) {
                    // Create jobseeker profile for users that don't have one
                    DB::table('jobseekers')->insert([
                        'user_id' => $profile->user_id,
                        'profile_status' => 'incomplete',
                        'profile_completion_percentage' => 0.00,
                        'country' => 'Philippines',
                        'salary_currency' => 'PHP',
                        'created_at' => $profile->created_at ?? now(),
                        'updated_at' => $profile->updated_at ?? now(),
                    ]);
                }
                
                // If there are skills in the old profile, merge them
                if ($profile->skills && $existingJobseeker) {
                    $oldSkills = json_decode($profile->skills, true);
                    if (is_array($oldSkills) && !empty($oldSkills)) {
                        $currentSkills = json_decode($existingJobseeker->skills, true) ?? [];
                        $mergedSkills = array_unique(array_merge($currentSkills, $oldSkills));
                        
                        DB::table('jobseekers')
                            ->where('user_id', $profile->user_id)
                            ->update(['skills' => json_encode($mergedSkills)]);
                    }
                }
            }
            
            echo "Migrated data from job_seeker_profiles to jobseekers table.\n";
            
        } catch (Exception $e) {
            echo "Warning: Could not migrate job_seeker_profiles data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Consolidate application status histories
     */
    private function consolidateApplicationStatusHistories(): void
    {
        try {
            // The application_status_histories table has good data structure
            // Just ensure it has proper indexes and constraints
            if (Schema::hasTable('application_status_histories')) {
                // Add any missing indexes
                Schema::table('application_status_histories', function ($table) {
                    if (!Schema::hasColumn('application_status_histories', 'created_at')) {
                        $table->timestamp('created_at')->nullable();
                    }
                    if (!Schema::hasColumn('application_status_histories', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }
            
        } catch (Exception $e) {
            echo "Warning: Could not optimize application_status_histories: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Clean up other redundant tables
     */
    private function cleanupRedundantTables(): void
    {
        // Note: Keep categories table as it's still referenced by jobs table via foreign key
        // Removing it would break the application

        // Clean up unused role/permission tables if they're empty
        // Drop in correct order to avoid foreign key constraints
        $authTablesToCheck = [
            'role_permissions', // Child table first
            'user_roles',       // Child table first
            'permissions',      // Parent table
            'roles'            // Parent table
        ];

        foreach ($authTablesToCheck as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count == 0) {
                    // Disable foreign key checks temporarily
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    Schema::dropIfExists($table);
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    echo "Dropped empty table: {$table}\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse table drops - would need to recreate from backups
        echo "Warning: Cannot reverse table drops. Restore from backup if needed.\n";
    }
};

