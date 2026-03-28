<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_id' => \App\Models\Pet::factory(),
            'activity_type_id' => \App\Models\ActivityType::factory(),
            'user_id' => \App\Models\User::factory(),
            'value' => fake()->word(),
            'started_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'notes' => fake()->sentence(),
        ];
    }
}
