<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FastJobSeekerSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating 30 job seekers...');

        $timestamp = time();
        $password = Hash::make('password123');

        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Carlos', 'Elena', 'Miguel', 'Sofia', 'Antonio', 'Carmen', 'Luis', 'Patricia', 'Roberto'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Torres', 'Flores', 'Lopez', 'Gonzales', 'Hernandez', 'Martinez'];
        $skills = ['PHP', 'Laravel', 'JavaScript', 'React', 'Python', 'Excel', 'Word', 'Communication', 'Sales', 'Customer Service', 'Accounting', 'Nursing', 'Teaching', 'Driving', 'AutoCAD'];
        $barangays = ['Poblacion', 'Zone I', 'Zone II', 'Zone III', 'Astorga', 'Darong', 'Inawayan', 'Sibulan'];
        $expLevels = ['entry', 'intermediate', 'expert'];

        for ($i = 1; $i <= 30; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $lastName;
            $uid = $timestamp . '_' . $i;

            // Insert user
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => 'jobseeker' . $uid . '@test.com',
                'password' => $password,
                'role' => 'jobseeker',
                'email_verified_at' => now(),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Random skills (3-5 skills)
            $numSkills = rand(3, 5);
            $userSkills = array_rand(array_flip($skills), $numSkills);
            if (!is_array($userSkills)) $userSkills = [$userSkills];

            $lat = 6.8370 + (rand(-100, 100) / 10000);
            $lng = 125.4130 + (rand(-100, 100) / 10000);
            $barangay = $barangays[array_rand($barangays)];

            // Insert job seeker profile
            DB::table('job_seeker_profiles')->insert([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => '09' . rand(100000000, 999999999),
                'address' => 'Brgy. ' . $barangay . ', Santa Cruz, Davao del Sur',
                'city' => 'Santa Cruz',
                'barangay' => $barangay,
                'latitude' => $lat,
                'longitude' => $lng,
                'bio' => 'Experienced professional looking for opportunities in Santa Cruz, Davao del Sur.',
                'skills' => json_encode($userSkills),
                'experience_level' => $expLevels[array_rand($expLevels)],
                'expected_salary_min' => rand(15, 25) * 1000,
                'expected_salary_max' => rand(30, 50) * 1000,
                'is_available' => 1,
                'profile_completion' => rand(70, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("Created: {$name}");
        }

        $this->command->info('Done! Created 30 job seekers.');
    }
}
