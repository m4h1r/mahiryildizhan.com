<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;

class IdempotencyTest extends AliceTestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
    }

    public function test_same_idempotency_key_returns_same_response(): void
    {
        $payload = [
            'date' => '2026-06-15',
            'amount' => 500.00,
            'currency_id' => $this->currency->id,
        ];

        $headers = ['Idempotency-Key' => 'test-key-'.uniqid()];

        // First request
        $first = $this->alicePost('incomes', $payload, $headers);
        $first->assertStatus(201);

        // Second request — same key
        $second = $this->alicePost('incomes', $payload, $headers);
        $second->assertStatus(201)
            ->assertHeader('X-Idempotent-Replayed', 'true');

        // Only one record should exist
        $this->assertDatabaseCount('incomes', 1);
    }

    public function test_different_idempotency_keys_create_separate_records(): void
    {
        $payload = [
            'date' => '2026-06-15',
            'amount' => 500.00,
            'currency_id' => $this->currency->id,
        ];

        $this->alicePost('incomes', $payload, ['Idempotency-Key' => 'key-1']);
        $this->alicePost('incomes', $payload, ['Idempotency-Key' => 'key-2']);

        $this->assertDatabaseCount('incomes', 2);
    }

    public function test_without_idempotency_key_creates_duplicate(): void
    {
        $payload = [
            'date' => '2026-06-15',
            'amount' => 500.00,
            'currency_id' => $this->currency->id,
        ];

        $this->alicePost('incomes', $payload)->assertStatus(201);
        $this->alicePost('incomes', $payload)->assertStatus(201);

        $this->assertDatabaseCount('incomes', 2);
    }
}
