<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TodoItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->text(200),
            'cost_try' => fake()->optional()->randomFloat(2, 10, 5000),
            'time_cost_hours' => fake()->optional()->randomFloat(2, 0.5, 40),
            'due_date' => fake()->optional()->date(),
            'is_bucketlist' => false,
            'is_completed' => false,
        ];
    }
}
