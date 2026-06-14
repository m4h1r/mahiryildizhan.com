<?php

namespace Tests\Feature\Alice;

use App\Models\Currency;

class MetaEndpointsTest extends AliceTestCase
{
    public function test_currencies_endpoint_returns_list(): void
    {
        Currency::create(['code' => 'TRY', 'name' => 'Türk Lirası', 'symbol' => '₺']);
        Currency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);

        $response = $this->aliceGet('meta/currencies');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'code', 'name', 'symbol']]]);
    }

    public function test_meta_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/alice/meta/currencies')
            ->assertStatus(401);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/v1/alice/health', $this->aliceHeaders())
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }
}
