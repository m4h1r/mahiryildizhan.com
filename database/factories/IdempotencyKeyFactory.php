<?php

namespace Database\Factories;

use App\Models\IdempotencyKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdempotencyKey>
 */
class IdempotencyKeyFactory extends Factory
{
    protected $model = IdempotencyKey::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'method' => $this->faker->randomElement(['POST', 'PUT', 'PATCH', 'DELETE']),
            'path' => '/alice/'.$this->faker->slug(),
            'status_code' => 200,
            'response_body' => ['ok' => true],
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ];
    }
}
