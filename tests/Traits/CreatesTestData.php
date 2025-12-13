<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Employer;
use App\Models\Jobseeker;
use App\Models\JobApplication;

trait CreatesTestData
{
    /**
     * Create a jobseeker user with profile.
     */
    protected function createJobseeker(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'jobseeker',
        ], $attributes));

        Jobseeker::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Create an employer user with profile.
     */
    protected function createEmployer(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'employer',
        ], $attributes));

        Employer::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Create an admin user.
     */
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
        ], $attributes));
    }

    /**
     * Create a job posting.
     */
    protected function createJob(array $attributes = []): Job
    {
        $this->ensureCategoriesExist();
        $this->ensureJobTypesExist();

        return Job::factory()->create(array_merge([
            'status' => 1,
        ], $attributes));
    }

    /**
     * Create multiple jobs.
     */
    protected function createJobs(int $count, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureCategoriesExist();
        $this->ensureJobTypesExist();

        return Job::factory()->count($count)->create(array_merge([
            'status' => 1,
        ], $attributes));
    }

    /**
     * Create a job application.
     */
    protected function createApplication(array $attributes = []): JobApplication
    {
        return JobApplication::factory()->create($attributes);
    }

    /**
     * Ensure categories exist in the database.
     */
    protected function ensureCategoriesExist(): void
    {
        if (Category::count() === 0) {
            Category::factory()->count(5)->create();
        }
    }

    /**
     * Ensure job types exist in the database.
     */
    protected function ensureJobTypesExist(): void
    {
        if (JobType::count() === 0) {
            JobType::factory()->count(5)->create();
        }
    }

    /**
     * Set up common test data.
     */
    protected function setUpTestData(): void
    {
        $this->ensureCategoriesExist();
        $this->ensureJobTypesExist();
    }
}
