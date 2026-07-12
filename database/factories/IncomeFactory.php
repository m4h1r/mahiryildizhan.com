<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'currency_id' => Currency::factory(),
            'amount' => fake()->randomFloat(2, 100, 20000),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
