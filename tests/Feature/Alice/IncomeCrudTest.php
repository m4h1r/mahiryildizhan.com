<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;
use App\Models\Income;

class IncomeCrudTest extends AliceTestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
    }

    public function test_index_returns_paginated_incomes(): void
    {
        Income::create(['date' => '2026-06-01', 'amount' => 5000.00, 'currency_id' => $this->currency->id]);

        $this->aliceGet('incomes')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_creates_income(): void
    {
        $this->alicePost('incomes', [
            'date' => '2026-06-15',
            'amount' => 10000.00,
            'description' => 'Freelance proje',
            'currency_id' => $this->currency->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.amount_display', '10.000,00 ₺');

        $this->assertDatabaseHas('incomes', ['description' => 'Freelance proje']);
    }

    public function test_store_validation_fails_without_amount(): void
    {
        $this->alicePost('incomes', ['date' => '2026-06-15', 'currency_id' => $this->currency->id])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_update_patches_income(): void
    {
        $income = Income::create(['date' => '2026-06-01', 'amount' => 1000, 'currency_id' => $this->currency->id]);

        $this->alicePatch("incomes/{$income->id}", ['description' => 'Proje ödemesi'])
            ->assertStatus(200)
            ->assertJsonPath('data.description', 'Proje ödemesi');
    }

    public function test_destroy_soft_deletes_income(): void
    {
        $income = Income::create(['date' => '2026-06-01', 'amount' => 1000, 'currency_id' => $this->currency->id]);

        $this->aliceDelete("incomes/{$income->id}")->assertStatus(200);
        $this->assertSoftDeleted('incomes', ['id' => $income->id]);
    }
}
