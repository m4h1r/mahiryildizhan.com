<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseType;

class ExpenseCrudTest extends AliceTestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/alice/expenses');
        $response->assertStatus(401);
    }

    public function test_index_returns_paginated_expenses(): void
    {
        Expense::create([
            'date' => '2026-06-01',
            'price' => 500.00,
            'quantity' => 1,
            'tax' => 0,
            'total' => 500.00,
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->aliceGet('expenses');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_creates_expense(): void
    {
        $response = $this->alicePost('expenses', [
            'date' => '2026-06-15',
            'description' => 'Market alışverişi',
            'price' => 250.50,
            'quantity' => 1,
            'tax' => 0,
            'total' => 250.50,
            'currency_id' => $this->currency->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.description', 'Market alışverişi')
            ->assertJsonPath('data.total', '250.50');

        $this->assertDatabaseHas('expenses', ['description' => 'Market alışverişi']);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $response = $this->alicePost('expenses', []);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['fields']]);
    }

    public function test_show_returns_expense(): void
    {
        $expense = Expense::create([
            'date' => '2026-06-01',
            'price' => 100,
            'quantity' => 1,
            'tax' => 0,
            'total' => 100,
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->aliceGet("expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $expense->id);
    }

    public function test_show_returns_404_for_missing_expense(): void
    {
        $this->aliceGet('expenses/99999')->assertStatus(404);
    }

    public function test_update_patches_expense(): void
    {
        $expense = Expense::create([
            'date' => '2026-06-01',
            'price' => 100,
            'quantity' => 1,
            'tax' => 0,
            'total' => 100,
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->alicePatch("expenses/{$expense->id}", [
            'description' => 'Güncellendi',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'Güncellendi');
    }

    public function test_destroy_soft_deletes_expense(): void
    {
        $expense = Expense::create([
            'date' => '2026-06-01',
            'price' => 100,
            'quantity' => 1,
            'tax' => 0,
            'total' => 100,
            'currency_id' => $this->currency->id,
        ]);

        $this->aliceDelete("expenses/{$expense->id}")->assertStatus(200);

        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_filter_by_date_range(): void
    {
        Expense::create(['date' => '2026-01-01', 'price' => 100, 'quantity' => 1, 'tax' => 0, 'total' => 100, 'currency_id' => $this->currency->id]);
        Expense::create(['date' => '2026-06-15', 'price' => 200, 'quantity' => 1, 'tax' => 0, 'total' => 200, 'currency_id' => $this->currency->id]);

        $response = $this->aliceGet('expenses', ['from' => '2026-06-01', 'to' => '2026-06-30']);

        $response->assertStatus(200)->assertJsonPath('meta.total', 1);
    }

    public function test_amount_display_field_is_formatted(): void
    {
        $expense = Expense::create([
            'date' => '2026-06-15',
            'price' => 1250.50,
            'quantity' => 1,
            'tax' => 0,
            'total' => 1250.50,
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->aliceGet("expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_display', '1.250,50 ₺');
    }
}
