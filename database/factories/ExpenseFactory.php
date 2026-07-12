<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 5000);

        return [
            'date' => fake()->date(),
            'currency_id' => Currency::factory(),
            'description' => fake()->optional()->sentence(),
            'price' => $price,
            'quantity' => 1,
            'tax' => 0,
            'total' => $price,
            'company_expense' => false,
            'paid_by_others' => false,
        ];
    }
}
