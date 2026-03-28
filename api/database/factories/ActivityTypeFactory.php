<?php

namespace Database\Factories;

use App\Models\ActivityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityType>
 */
class ActivityTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'household_id' => \App\Models\Household::factory(),
            'name' => fake()->word(),
            'type' => fake()->randomElement(['boolean', 'value', 'timer']),
            'icon' => fake()->word(),
            'is_fast_action' => fake()->boolean(),
        ];
    }
}
