<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;
use App\Models\Income;

class AuditLogTest extends AliceTestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
    }

    public function test_post_creates_audit_log_entry(): void
    {
        $this->alicePost('incomes', [
            'date' => '2026-06-15',
            'amount' => 500.00,
            'currency_id' => $this->currency->id,
        ]);

        $this->assertDatabaseHas('alice_audit_log', [
            'action' => 'created',
            'source' => 'alice',
        ]);
    }

    public function test_patch_creates_audit_log_entry(): void
    {
        $income = Income::create(['date' => '2026-06-01', 'amount' => 1000, 'currency_id' => $this->currency->id]);

        $this->alicePatch("incomes/{$income->id}", ['description' => 'Test']);

        $this->assertDatabaseHas('alice_audit_log', [
            'action' => 'updated',
        ]);
    }

    public function test_delete_creates_audit_log_entry(): void
    {
        $income = Income::create(['date' => '2026-06-01', 'amount' => 1000, 'currency_id' => $this->currency->id]);

        $this->aliceDelete("incomes/{$income->id}");

        $this->assertDatabaseHas('alice_audit_log', [
            'action' => 'deleted',
        ]);
    }

    public function test_get_does_not_create_audit_log(): void
    {
        $this->aliceGet('meta/currencies');

        $this->assertDatabaseCount('alice_audit_log', 0);
    }

    public function test_alice_source_header_is_logged(): void
    {
        $this->alicePost('incomes', [
            'date' => '2026-06-15',
            'amount' => 500.00,
            'currency_id' => $this->currency->id,
        ], ['X-Alice-Source' => 'telegram-bot']);

        $this->assertDatabaseHas('alice_audit_log', [
            'source' => 'telegram-bot',
        ]);
    }
}
