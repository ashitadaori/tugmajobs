<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FastEmployerSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Clearing old data...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('jobs')->truncate();
        DB::table('employers')->truncate();
        DB::table('users')->where('role', '!=', 'admin')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Creating employers and jobs...');

        $timestamp = time();
        $password = Hash::make('password123');

        $companies = [
            'Dole Philippines', 'Del Monte Fresh', 'Santa Cruz Hospital', 'Davao Medical Center',
            'Gaisano Mall', 'NCCC Supermarket', 'BDO Unibank', 'Metrobank Branch',
            'Holy Cross College', 'UM Digos Campus', 'Jollibee Foods', 'Max Restaurant',
            'Globe Telecom', 'Smart Communications', 'DepEd Division', 'Municipal Hall',
            'Toyota Motors', 'Honda Philippines', 'Mercury Drug', 'Robinsons Land'
        ];

        $industries = ['Agriculture', 'Healthcare', 'Retail', 'Banking', 'Education', 'Food', 'Telecom', 'Government', 'Manufacturing', 'Construction'];
        $barangays = ['Poblacion', 'Zone I', 'Zone II', 'Zone III', 'Astorga', 'Darong'];
        $jobTitles = ['Software Developer', 'Sales Rep', 'Accountant', 'HR Officer', 'Nurse', 'Engineer', 'Admin Assistant', 'Driver', 'Cashier', 'Teacher'];

        foreach ($companies as $i => $name) {
            $uid = $timestamp . '_' . ($i + 1);
            $slug = Str::slug($name) . '-' . $uid;

            // Insert user
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => 'emp' . $uid . '@test.com',
                'password' => $password,
                'role' => 'employer',
                'email_verified_at' => now(),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert employer
            DB::table('employers')->insert([
                'user_id' => $userId,
                'company_name' => $name,
                'company_slug' => $slug,
                'company_description' => $name . ' is located in Santa Cruz, Davao del Sur.',
                'industry' => $industries[$i % count($industries)],
                'founded_year' => rand(1995, 2020),
                'company_size' => '11-50',
                'business_address' => 'Santa Cruz, Davao del Sur',
                'city' => 'Santa Cruz',
                'country' => 'Philippines',
                'postal_code' => '8001',
                'contact_person_name' => 'HR Dept',
                'business_email' => 'hr' . $uid . '@test.com',
                'business_phone' => '082-123-4567',
                'is_verified' => 1,
                'verified_at' => now(),
                'status' => 'active',
                'subscription_plan' => 'basic',
                'job_posts_limit' => 10,
                'job_posts_used' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert 4 jobs per employer
            for ($j = 0; $j < 4; $j++) {
                $lat = 6.8370 + (rand(-50, 50) / 10000);
                $lng = 125.4130 + (rand(-50, 50) / 10000);
                $barangay = $barangays[array_rand($barangays)];

                DB::table('jobs')->insert([
                    'employer_id' => $userId,
                    'category_id' => rand(1, 7),
                    'job_type_id' => rand(1, 3),
                    'company_name' => $name,
                    'title' => $jobTitles[array_rand($jobTitles)],
                    'description' => 'We are hiring for this position at ' . $name,
                    'requirements' => '1-3 years experience required',
                    'salary_min' => rand(15, 30) * 1000,
                    'salary_max' => rand(30, 50) * 1000,
                    'location' => 'Brgy. ' . $barangay . ', Santa Cruz',
                    'location_name' => 'Santa Cruz, Davao del Sur',
                    'address' => 'Santa Cruz, Davao del Sur',
                    'city' => 'Santa Cruz',
                    'barangay' => $barangay,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'experience_level' => 'entry',
                    'vacancy' => rand(1, 3),
                    'status' => 1,
                    'approved_at' => now(),
                    'deadline' => now()->addDays(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("Created: {$name}");
        }

        $this->command->info('Done! Created 20 employers with 80 jobs.');
    }
}
