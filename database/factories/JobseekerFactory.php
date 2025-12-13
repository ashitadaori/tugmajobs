<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jobseeker>
 */
class JobseekerFactory extends Factory
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
            'middle_name' => fake()->optional()->lastName(),
            'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality' => 'Filipino',
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced']),
            'phone' => fake()->phoneNumber(),
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->address(),
            'city' => 'Sta. Cruz',
            'state' => 'Davao del Sur',
            'country' => 'Philippines',
            'postal_code' => fake()->postcode(),
        ];
    }
}
