<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'model_type' => Person::class,
            'model_id' => fake()->numberBetween(1, 1000),
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'changes' => null,
        ];
    }
}
