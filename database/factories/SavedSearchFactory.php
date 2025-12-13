<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'criteria' => [
                'q' => fake()->jobTitle(),
                'location' => fake()->city(),
                'category_id' => rand(1, 5),
            ],
            'notifications_enabled' => fake()->boolean(30),
        ];
    }

    /**
     * Enable notifications for this saved search.
     */
    public function withNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'notifications_enabled' => true,
        ]);
    }
}
