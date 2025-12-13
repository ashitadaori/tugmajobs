<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployerProfile>
 */
class EmployerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory()->create(['role' => 'employer']),
            'company_name' => fake()->company(),
            'company_description' => fake()->paragraph(),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Education']),
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'website' => fake()->url(),
            'company_logo' => null,
            'location' => 'Sta. Cruz, Davao del Sur',
            'social_links' => json_encode([
                'facebook' => fake()->url(),
                'twitter' => fake()->url(),
                'linkedin' => fake()->url(),
            ]),
            'status' => 'active',
        ];
    }
}
