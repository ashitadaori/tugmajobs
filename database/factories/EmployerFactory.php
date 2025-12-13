<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employer>
 */
class EmployerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $companyName = fake()->company();

        return [
            'user_id' => \App\Models\User::factory()->create(['role' => 'employer']),
            'company_name' => $companyName,
            'company_slug' => \Illuminate\Support\Str::slug($companyName),
            'company_description' => fake()->paragraph(),
            'company_website' => fake()->url(),
            'company_logo' => null,
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Education']),
            'founded_year' => fake()->year(),
            'contact_person_name' => fake()->name(),
            'contact_person_email' => fake()->companyEmail(),
            'contact_person_phone' => fake()->phoneNumber(),
        ];
    }
}
