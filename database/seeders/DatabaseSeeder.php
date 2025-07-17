<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\JobType;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            JobTypeSeeder::class,
        ]);

        // Create default job types
        $jobTypes = [
            ['name' => 'Full Time', 'status' => 1],
            ['name' => 'Part Time', 'status' => 1],
            ['name' => 'Contract', 'status' => 1],
            ['name' => 'Temporary', 'status' => 1],
            ['name' => 'Internship', 'status' => 1],
            ['name' => 'Remote', 'status' => 1],
            ['name' => 'Freelance', 'status' => 1],
            ['name' => 'Volunteer', 'status' => 1]
        ];

        foreach ($jobTypes as $jobType) {
            JobType::updateOrCreate(
                ['slug' => Str::slug($jobType['name'])],
                [
                    'name' => $jobType['name'],
                    'status' => $jobType['status']
                ]
            );
        }
    }
}
