<?php

namespace Database\Factories;

use App\Models\AliceAuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AliceAuditLog>
 */
class AliceAuditLogFactory extends Factory
{
    protected $model = AliceAuditLog::class;

    public function definition(): array
    {
        return [
            'action' => $this->faker->randomElement(['create', 'update', 'delete']),
            'table_name' => $this->faker->randomElement(['people', 'incomes', 'expenses']),
            'record_id' => $this->faker->numberBetween(1, 1000),
            'old_data' => null,
            'new_data' => ['field' => $this->faker->word()],
            'source' => 'alice',
            'ip' => $this->faker->ipv4(),
            'idempotency_key' => $this->faker->uuid(),
            'user_agent' => $this->faker->userAgent(),
            'dry_run' => false,
            'created_at' => now(),
        ];
    }
}
