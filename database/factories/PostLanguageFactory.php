<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Turkish', 'English', 'German', 'French', 'Spanish']),
            'code' => fake()->unique()->languageCode(),
        ];
    }
}
