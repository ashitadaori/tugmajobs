<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $job = \App\Models\Job::factory()->create();

        return [
            'job_id' => $job->id,
            'user_id' => \App\Models\User::factory()->create(['role' => 'jobseeker']),
            'employer_id' => $job->user_id,
            'status' => fake()->randomElement(['pending', 'reviewed', 'interview', 'rejected', 'accepted']),
            'shortlisted' => false,
            'cover_letter' => fake()->paragraph(),
            'resume' => null,
            'notes' => null,
            'applied_date' => now(),
            'preliminary_answers' => null,
            'rejection_reason' => null,
            'rejection_feedback' => null,
        ];
    }
}
