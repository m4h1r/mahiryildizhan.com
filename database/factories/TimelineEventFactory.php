<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TimelineEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'event_type' => 'milestone',
            'start_date' => fake()->date(),
            'is_public' => true,
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
