<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'text_color' => fake()->optional()->hexColor(),
            'text_size' => fake()->optional()->randomElement(['sm', 'md', 'lg']),
        ];
    }
}
