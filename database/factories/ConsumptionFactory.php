<?php

namespace Database\Factories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsumptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'food_id' => Food::factory(),
            'consumed_on' => fake()->date(),
            'quantity' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
