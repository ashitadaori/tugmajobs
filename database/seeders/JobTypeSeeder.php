<?php

namespace Database\Seeders;

use App\Models\JobType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobTypeSeeder extends Seeder
{
    public function run(): void
    {
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