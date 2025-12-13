<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration safely moves job seeker and employer specific data
     * from the users table to their respective profile tables.
     *
     * @return void
     */
    public function up()
    {
        // 1. Migrate Job Seeker data from users to jobseekers
        $this->migrateJobSeekerData();

        // 2. Consolidate Employer data from employer_profiles to employers
        $this->consolidateEmployerData();
    }

    /**
     * Migrate job seeker specific data from users table to jobseekers table
     */
    private function migrateJobSeekerData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all job seekers
        $jobSeekers = DB::table('users')
            ->where('role', 'jobseeker')
            ->get();

        foreach ($jobSeekers as $user) {
            // Check if profile exists
            $profile = DB::table('jobseekers')->where('user_id', $user->id)->first();

            if ($profile) {
                // Update existing profile with data from users table
                $updateData = [];

                // Only update if the profile field is empty and user field has data
                if (empty($profile->skills) && !empty($user->skills)) {
                    $updateData['skills'] = $user->skills;
                }

                if (empty($profile->professional_summary) && !empty($user->bio)) {
                    $updateData['professional_summary'] = $user->bio;
                }

                if (empty($profile->current_address) && !empty($user->address)) {
                    $updateData['current_address'] = $user->address;
                }

                if (empty($profile->city) && !empty($user->location)) {
                    // Try to extract city from location string
                    $updateData['city'] = $user->location;
                }

                if (empty($profile->current_job_title) && !empty($user->job_title)) {
                    $updateData['current_job_title'] = $user->job_title;
                } elseif (empty($profile->current_job_title) && !empty($user->designation)) {
                    $updateData['current_job_title'] = $user->designation;
                }

                if (empty($profile->total_experience_years) && !empty($user->experience_years)) {
                    $updateData['total_experience_years'] = (int)$user->experience_years;
                }

                if (empty($profile->education) && !empty($user->education)) {
                    // Convert text education to JSON if needed
                    $updateData['education'] = is_string($user->education)
                        ? json_encode([['degree' => $user->education]])
                        : $user->education;
                }

                if (empty($profile->languages) && !empty($user->language)) {
                    // Convert to JSON array if string
                    $updateData['languages'] = is_string($user->language)
                        ? json_encode([$user->language])
                        : $user->language;
                }

                if (empty($profile->preferred_job_types) && !empty($user->preferred_job_types)) {
                    $updateData['preferred_job_types'] = $user->preferred_job_types;
                }

                if (empty($profile->preferred_categories) && !empty($user->preferred_categories)) {
                    $updateData['preferred_categories'] = $user->preferred_categories;
                }

                if (empty($profile->preferred_locations) && !empty($user->preferred_location)) {
                    // Convert single location to array
                    $updateData['preferred_locations'] = is_string($user->preferred_location)
                        ? json_encode([$user->preferred_location])
                        : $user->preferred_location;
                }

                if (empty($profile->expected_salary_min) && !empty($user->salary_expectation_min)) {
                    $updateData['expected_salary_min'] = $user->salary_expectation_min;
                }

                if (empty($profile->expected_salary_max) && !empty($user->salary_expectation_max)) {
                    $updateData['expected_salary_max'] = $user->salary_expectation_max;
                }

                if (empty($profile->resume_file) && !empty($user->resume)) {
                    $updateData['resume_file'] = $user->resume;
                }

                if (empty($profile->phone) && !empty($user->phone)) {
                    $updateData['phone'] = $user->phone;
                } elseif (empty($profile->phone) && !empty($user->mobile)) {
                    $updateData['phone'] = $user->mobile;
                }

                // Update if we have data to migrate
                if (!empty($updateData)) {
                    $updateData['updated_at'] = now();
                    DB::table('jobseekers')
                        ->where('user_id', $user->id)
                        ->update($updateData);
                }
            } else {
                // Create new profile with data from users table
                // Only insert non-null values to avoid constraint violations
                $insertData = [
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add only non-empty fields
                if (!empty($user->skills)) {
                    $insertData['skills'] = $user->skills;
                }
                if (!empty($user->bio)) {
                    $insertData['professional_summary'] = $user->bio;
                }
                if (!empty($user->address)) {
                    $insertData['current_address'] = $user->address;
                }
                if (!empty($user->location)) {
                    $insertData['city'] = $user->location;
                }
                if (!empty($user->job_title) || !empty($user->designation)) {
                    $insertData['current_job_title'] = $user->job_title ?? $user->designation;
                }
                if (!empty($user->experience_years)) {
                    $insertData['total_experience_years'] = (int)$user->experience_years;
                }
                if (!empty($user->education)) {
                    $insertData['education'] = is_string($user->education)
                        ? json_encode([['degree' => $user->education]])
                        : $user->education;
                }
                if (!empty($user->language)) {
                    $insertData['languages'] = is_string($user->language)
                        ? json_encode([$user->language])
                        : $user->language;
                }
                if (!empty($user->preferred_job_types)) {
                    $insertData['preferred_job_types'] = $user->preferred_job_types;
                }
                if (!empty($user->preferred_categories)) {
                    $insertData['preferred_categories'] = $user->preferred_categories;
                }
                if (!empty($user->preferred_location)) {
                    $insertData['preferred_locations'] = is_string($user->preferred_location)
                        ? json_encode([$user->preferred_location])
                        : $user->preferred_location;
                }
                if (!empty($user->salary_expectation_min)) {
                    $insertData['expected_salary_min'] = $user->salary_expectation_min;
                }
                if (!empty($user->salary_expectation_max)) {
                    $insertData['expected_salary_max'] = $user->salary_expectation_max;
                }
                if (!empty($user->resume)) {
                    $insertData['resume_file'] = $user->resume;
                }
                if (!empty($user->phone) || !empty($user->mobile)) {
                    $insertData['phone'] = $user->phone ?? $user->mobile;
                }

                DB::table('jobseekers')->insert($insertData);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        echo "✓ Migrated job seeker data for " . $jobSeekers->count() . " users\n";
    }

    /**
     * Consolidate employer data from employer_profiles to employers table
     */
    private function consolidateEmployerData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all employers
        $employers = DB::table('users')
            ->where('role', 'employer')
            ->get();

        foreach ($employers as $user) {
            // Check if employer profile exists in new table
            $newProfile = DB::table('employers')->where('user_id', $user->id)->first();

            // Check if legacy profile exists
            $legacyProfile = DB::table('employer_profiles')->where('user_id', $user->id)->first();

            if ($newProfile && $legacyProfile) {
                // Both exist - merge legacy data into new profile where fields are empty
                $updateData = [];

                // Merge fields from legacy profile that aren't in new employers table
                if (empty($newProfile->company_name) && !empty($legacyProfile->company_name)) {
                    $updateData['company_name'] = $legacyProfile->company_name;
                }

                if (empty($newProfile->company_description) && !empty($legacyProfile->company_description)) {
                    $updateData['company_description'] = $legacyProfile->company_description;
                }

                if (empty($newProfile->industry) && !empty($legacyProfile->industry)) {
                    $updateData['industry'] = $legacyProfile->industry;
                }

                if (empty($newProfile->company_size) && !empty($legacyProfile->company_size)) {
                    $updateData['company_size'] = $legacyProfile->company_size;
                }

                if (empty($newProfile->company_website) && !empty($legacyProfile->website)) {
                    $updateData['company_website'] = $legacyProfile->website;
                }

                if (empty($newProfile->company_logo) && !empty($legacyProfile->company_logo)) {
                    $updateData['company_logo'] = $legacyProfile->company_logo;
                }

                if (!empty($updateData)) {
                    $updateData['updated_at'] = now();
                    DB::table('employers')
                        ->where('user_id', $user->id)
                        ->update($updateData);
                }

            } elseif (!$newProfile && $legacyProfile) {
                // Only legacy exists - create new profile from legacy data
                DB::table('employers')->insert([
                    'user_id' => $user->id,
                    'company_name' => $legacyProfile->company_name ?? 'Company Name',
                    'company_description' => $legacyProfile->company_description,
                    'company_website' => $legacyProfile->website,
                    'company_logo' => $legacyProfile->company_logo,
                    'company_size' => $legacyProfile->company_size,
                    'industry' => $legacyProfile->industry,
                    'founded_year' => $legacyProfile->founded_year,
                    'business_address' => $legacyProfile->location,
                    'status' => $legacyProfile->status ?? 'published',
                    'is_verified' => $legacyProfile->is_verified ?? false,
                    'is_featured' => $legacyProfile->is_featured ?? false,
                    'created_at' => $legacyProfile->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            } elseif (!$newProfile && !$legacyProfile) {
                // Neither exists - create empty profile
                DB::table('employers')->insert([
                    'user_id' => $user->id,
                    'company_name' => 'Company Name',
                    'status' => 'draft',
                    'is_verified' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        echo "✓ Consolidated employer data for " . $employers->count() . " users\n";
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This will NOT restore data back to users table
     * This is intentional to prevent data loss
     *
     * @return void
     */
    public function down()
    {
        // We don't reverse data migrations to prevent data loss
        // The column removal migrations will handle cleanup
        echo "⚠ Data migration rollback skipped to prevent data loss\n";
        echo "  Profile table data remains intact\n";
    }
};
