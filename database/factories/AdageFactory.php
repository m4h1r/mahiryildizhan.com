<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner' => fake()->name(),
            'adage' => fake()->sentence(),
            'keywords' => fake()->optional()->words(3, true),
        ];
    }
}
