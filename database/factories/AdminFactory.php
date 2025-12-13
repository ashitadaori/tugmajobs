<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory()->create(['role' => 'admin']),
            'admin_level' => fake()->randomElement(['super', 'senior', 'junior']),
            'department' => fake()->randomElement(['HR', 'IT', 'Operations']),
            'position' => fake()->jobTitle(),
            'responsibilities' => fake()->paragraph(),
            'permissions' => json_encode(['manage_users', 'manage_jobs']),
            'accessible_modules' => json_encode(['dashboard', 'users', 'jobs']),
            'can_manage_users' => true,
            'can_manage_jobs' => true,
            'can_manage_employers' => true,
        ];
    }
}
