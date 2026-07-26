<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FoodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'calories_per_100g' => fake()->numberBetween(20, 900),
            'carbs_per_100g' => fake()->randomFloat(2, 0, 90),
            'sugar_per_100g' => fake()->randomFloat(2, 0, 30),
            'protein_per_100g' => fake()->randomFloat(2, 0, 40),
            'fat_per_100g' => fake()->randomFloat(2, 0, 90),
            'unit_type' => 'gram',
            'grams_per_unit' => null,
            'vitamins' => null,
        ];
    }
}
