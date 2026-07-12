<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class InteractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'date' => fake()->date(),
            'effect' => fake()->optional()->randomElement(['-2', '-1', '0', '1', '2']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
