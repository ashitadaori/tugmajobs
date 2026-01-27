<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employer;
use App\Models\Job;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanEmployerSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Step 1: Clearing old test data...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear jobs table
        DB::table('jobs')->truncate();
        $this->command->info('  - Jobs table cleared');

        // Clear employers table
        DB::table('employers')->truncate();
        $this->command->info('  - Employers table cleared');

        // Delete non-admin users
        DB::table('users')->where('role', '!=', 'admin')->delete();
        $this->command->info('  - Non-admin users deleted');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Step 2: Creating 20 employers with jobs...');

        $timestamp = time();

        $companies = [
            ['name' => 'Dole Philippines', 'industry' => 'Agriculture & Farming'],
            ['name' => 'Del Monte Fresh', 'industry' => 'Agriculture & Farming'],
            ['name' => 'Santa Cruz Hospital', 'industry' => 'Healthcare'],
            ['name' => 'Davao Medical Center', 'industry' => 'Healthcare'],
            ['name' => 'Gaisano Mall', 'industry' => 'Retail & Hospitality'],
            ['name' => 'NCCC Supermarket', 'industry' => 'Retail & Hospitality'],
            ['name' => 'BDO Unibank', 'industry' => 'Banking & Finance'],
            ['name' => 'Metrobank Branch', 'industry' => 'Banking & Finance'],
            ['name' => 'Holy Cross College', 'industry' => 'Education'],
            ['name' => 'UM Digos Campus', 'industry' => 'Education'],
            ['name' => 'Jollibee Foods', 'industry' => 'Food & Beverage'],
            ['name' => 'Max Restaurant', 'industry' => 'Food & Beverage'],
            ['name' => 'Globe Telecom', 'industry' => 'Telecommunications'],
            ['name' => 'Smart Communications', 'industry' => 'Telecommunications'],
            ['name' => 'DepEd Division Office', 'industry' => 'Government Services'],
            ['name' => 'Municipal Hall', 'industry' => 'Government Services'],
            ['name' => 'Toyota Motors', 'industry' => 'Manufacturing'],
            ['name' => 'Honda Philippines', 'industry' => 'Manufacturing'],
            ['name' => 'Mercury Drug', 'industry' => 'Retail & Hospitality'],
            ['name' => 'Robinsons Land', 'industry' => 'Construction'],
        ];

        $jobTemplates = [
            ['title' => 'Software Developer', 'category_id' => 1, 'salary_min' => 25000, 'salary_max' => 45000, 'skills' => ['PHP', 'Laravel', 'MySQL']],
            ['title' => 'Sales Representative', 'category_id' => 3, 'salary_min' => 15000, 'salary_max' => 25000, 'skills' => ['Sales', 'Communication']],
            ['title' => 'Accountant', 'category_id' => 4, 'salary_min' => 20000, 'salary_max' => 35000, 'skills' => ['Accounting', 'Excel']],
            ['title' => 'HR Officer', 'category_id' => 5, 'salary_min' => 18000, 'salary_max' => 30000, 'skills' => ['HR', 'Recruitment']],
            ['title' => 'Registered Nurse', 'category_id' => 7, 'salary_min' => 20000, 'salary_max' => 32000, 'skills' => ['Patient Care', 'Nursing']],
            ['title' => 'Civil Engineer', 'category_id' => 6, 'salary_min' => 28000, 'salary_max' => 45000, 'skills' => ['AutoCAD', 'Construction']],
            ['title' => 'Administrative Assistant', 'category_id' => 5, 'salary_min' => 15000, 'salary_max' => 22000, 'skills' => ['MS Office', 'Admin']],
            ['title' => 'Driver', 'category_id' => 13, 'salary_min' => 12000, 'salary_max' => 18000, 'skills' => ['Driving', 'Navigation']],
            ['title' => 'Cashier', 'category_id' => 4, 'salary_min' => 12000, 'salary_max' => 16000, 'skills' => ['Cash Handling', 'Customer Service']],
            ['title' => 'Teacher', 'category_id' => 5, 'salary_min' => 18000, 'salary_max' => 28000, 'skills' => ['Teaching', 'Communication']],
        ];

        $barangays = ['Poblacion', 'Zone I', 'Zone II', 'Zone III', 'Astorga', 'Darong', 'Inawayan', 'Sibulan'];

        foreach ($companies as $index => $company) {
            $uniqueId = $timestamp . '_' . ($index + 1);
            $slug = Str::slug($company['name']) . '-' . $uniqueId;
            $email = 'employer' . $uniqueId . '@test.com';

            // Create user
            $user = User::create([
                'name' => $company['name'],
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'employer',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Create employer profile
            $employer = Employer::create([
                'user_id' => $user->id,
                'company_name' => $company['name'],
                'company_slug' => $slug,
                'company_description' => $company['name'] . ' is a leading company in ' . $company['industry'] . ' located in Santa Cruz, Davao del Sur.',
                'industry' => $company['industry'],
                'founded_year' => rand(1990, 2020),
                'company_size' => ['1-10', '11-50', '51-200'][rand(0, 2)],
                'business_address' => 'Brgy. ' . $barangays[array_rand($barangays)] . ', Santa Cruz, Davao del Sur',
                'city' => 'Santa Cruz, Davao del Sur',
                'country' => 'Philippines',
                'postal_code' => '8001',
                'contact_person_name' => 'HR Department',
                'contact_person_designation' => 'HR Manager',
                'business_email' => 'hr' . $uniqueId . '@test.com',
                'business_phone' => '082-' . rand(100, 999) . '-' . rand(1000, 9999),
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
                'subscription_plan' => 'basic',
                'job_posts_limit' => 10,
                'job_posts_used' => 0,
            ]);

            // Create 3-5 jobs per employer
            $numJobs = rand(3, 5);
            $jobsCreated = 0;

            for ($j = 0; $j < $numJobs; $j++) {
                $template = $jobTemplates[array_rand($jobTemplates)];
                $barangay = $barangays[array_rand($barangays)];

                // Base coordinates for Santa Cruz, Davao del Sur
                $lat = 6.8370 + (rand(-100, 100) / 10000);
                $lng = 125.4130 + (rand(-100, 100) / 10000);

                Job::create([
                    'employer_id' => $user->id,
                    'category_id' => $template['category_id'],
                    'job_type_id' => rand(1, 3),
                    'company_name' => $company['name'],
                    'title' => $template['title'],
                    'description' => 'We are looking for a ' . $template['title'] . ' to join our team at ' . $company['name'] . '. Skills required: ' . implode(', ', $template['skills']),
                    'requirements' => '• 1-3 years experience\n• Proficiency in: ' . implode(', ', $template['skills']) . '\n• Good communication skills',
                    'salary_min' => $template['salary_min'],
                    'salary_max' => $template['salary_max'],
                    'location' => 'Brgy. ' . $barangay . ', Santa Cruz, Davao del Sur',
                    'location_name' => 'Santa Cruz, Davao del Sur',
                    'address' => 'Santa Cruz, Davao del Sur, Philippines',
                    'city' => 'Santa Cruz',
                    'barangay' => $barangay,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'experience_level' => ['entry', 'intermediate', 'expert'][rand(0, 2)],
                    'vacancy' => rand(1, 3),
                    'status' => 1,
                    'approved_at' => now()->subDays(rand(1, 30)),
                    'deadline' => now()->addDays(rand(15, 60)),
                ]);
                $jobsCreated++;
            }

            $employer->update(['job_posts_used' => $jobsCreated]);
            $this->command->info("Created: {$company['name']} with {$jobsCreated} jobs");
        }

        $totalJobs = Job::count();
        $totalEmployers = Employer::count();

        $this->command->info('');
        $this->command->info("Done! Created {$totalEmployers} employers with {$totalJobs} jobs.");
    }
}
