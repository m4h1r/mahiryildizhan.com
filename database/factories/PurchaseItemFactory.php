<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'cost_try' => fake()->optional()->randomFloat(2, 10, 5000),
            'is_bucketlist' => false,
            'is_completed' => false,
        ];
    }
}
