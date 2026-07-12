<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StakeholderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vkn_tckn' => fake()->unique()->numerify('###########'),
            'title' => fake()->company(),
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'city' => fake()->city(),
            'country' => 'TR',
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'company_type' => 'Company',
            'status' => 'Active',
        ];
    }
}
