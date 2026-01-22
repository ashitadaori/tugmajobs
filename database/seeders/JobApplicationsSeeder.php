<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobApplication;
use App\Models\Job;
use App\Models\User;

class JobApplicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample job applications for testing the analytics features
     */
    public function run()
    {
        $this->command->info('Creating sample job applications...');

        // Get all approved jobs
        $jobs = Job::where('status', 1)->get();

        // Get all jobseekers
        $jobseekers = User::where('role', 'jobseeker')->get();

        if ($jobs->isEmpty()) {
            $this->command->warn('No jobs found. Please run EmployersSeeder first.');
            return;
        }

        if ($jobseekers->isEmpty()) {
            $this->command->warn('No jobseekers found. Please run JobSeekersSeeder first.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $stages = ['application', 'requirements', 'interview', 'hired', 'rejected'];
        $applicationCount = 0;

        // Each jobseeker applies to 3-8 random jobs
        foreach ($jobseekers as $jobseeker) {
            $numApplications = rand(3, 8);
            $appliedJobs = $jobs->random(min($numApplications, $jobs->count()));

            foreach ($appliedJobs as $job) {
                // Skip if already applied
                $exists = JobApplication::where('job_id', $job->id)
                    ->where('user_id', $jobseeker->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $daysAgo = rand(1, 45);
                $status = $statuses[array_rand($statuses)];
                $stage = $stages[array_rand($stages)];

                JobApplication::create([
                    'job_id' => $job->id,
                    'user_id' => $jobseeker->id,
                    'employer_id' => $job->employer_id,
                    'status' => $status,
                    'stage' => $stage,
                    'stage_status' => $status === 'pending' ? 'pending' : ($status === 'approved' ? 'approved' : 'rejected'),
                    'shortlisted' => $status === 'approved' && rand(0, 100) > 50,
                    'cover_letter' => $this->generateCoverLetter($jobseeker->name, $job->title),
                    'applied_date' => now()->subDays($daysAgo),
                    'application_step' => rand(1, 3),
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays(rand(0, $daysAgo)),
                ]);

                $applicationCount++;
            }
        }

        $this->command->info("Successfully created {$applicationCount} job applications!");

        // Display distribution by barangay
        $this->command->info('');
        $this->command->info('Application distribution by barangay:');

        $distribution = JobApplication::join('jobs', 'job_applications.job_id', '=', 'jobs.id')
            ->whereNotNull('jobs.barangay')
            ->selectRaw('jobs.barangay, COUNT(*) as count')
            ->groupBy('jobs.barangay')
            ->orderByDesc('count')
            ->get();

        foreach ($distribution as $row) {
            $this->command->info("  {$row->barangay}: {$row->count} applications");
        }
    }

    private function generateCoverLetter($applicantName, $jobTitle)
    {
        $templates = [
            "I am writing to express my strong interest in the {$jobTitle} position. With my relevant skills and experience, I am confident I can contribute effectively to your team. I am eager to bring my dedication and expertise to this role.",
            "I am excited to apply for the {$jobTitle} position at your company. My background and skills align well with the requirements of this role, and I am enthusiastic about the opportunity to contribute to your organization.",
            "I am submitting my application for the {$jobTitle} position. I believe my qualifications and experience make me a strong candidate for this role. I am committed to delivering quality work and contributing to the success of your team.",
            "I would like to apply for the {$jobTitle} position. I have developed relevant skills that I believe would be valuable in this role. I am a dedicated professional looking to grow and contribute to a dynamic team.",
        ];

        return $templates[array_rand($templates)];
    }
}
