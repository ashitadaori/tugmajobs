<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobseekerProfile>
 */
class JobseekerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory()->create(['role' => 'jobseeker']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'city' => 'Sta. Cruz',
            'state' => 'Davao del Sur',
            'country' => 'Philippines',
            'professional_summary' => fake()->paragraph(),
            'skills' => 'PHP, Laravel, MySQL, JavaScript, HTML, CSS',
            'education' => json_encode([
                [
                    'degree' => 'Bachelor of Science in Computer Science',
                    'institution' => 'University of the Philippines',
                    'year' => '2020',
                ]
            ]),
            'work_experience' => json_encode([
                [
                    'position' => 'Software Developer',
                    'company' => fake()->company(),
                    'duration' => '2020-2023',
                    'description' => fake()->paragraph(),
                ]
            ]),
            'certifications' => json_encode([]),
            'languages' => json_encode(['English', 'Filipino']),
            'resume_path' => null,
            'profile_picture' => null,
            'linkedin_url' => fake()->optional()->url(),
            'github_url' => fake()->optional()->url(),
            'portfolio_url' => fake()->optional()->url(),
        ];
    }
}
