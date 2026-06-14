<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;

class DryRunTest extends AliceTestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
    }

    public function test_dry_run_returns_success_without_writing(): void
    {
        $response = $this->postJson(
            '/api/v1/alice/incomes?dry_run=1',
            [
                'date' => '2026-06-15',
                'amount' => 500.00,
                'currency_id' => $this->currency->id,
            ],
            $this->aliceHeaders()
        );

        $response->assertStatus(201)
            ->assertJsonPath('dry_run', true)
            ->assertJsonFragment(['dry_run_note' => 'Bu işlem simülasyon modunda çalıştı. Veritabanına hiçbir şey yazılmadı.']);

        // Nothing should be in DB
        $this->assertDatabaseCount('incomes', 0);
    }

    public function test_dry_run_false_does_write(): void
    {
        $this->postJson(
            '/api/v1/alice/incomes?dry_run=0',
            ['date' => '2026-06-15', 'amount' => 500.00, 'currency_id' => $this->currency->id],
            $this->aliceHeaders()
        )->assertStatus(201);

        $this->assertDatabaseCount('incomes', 1);
    }

    public function test_dry_run_get_request_is_ignored(): void
    {
        // dry_run on GET should have no effect
        $this->aliceGet('meta/currencies', ['dry_run' => '1'])
            ->assertStatus(200);
    }
}
