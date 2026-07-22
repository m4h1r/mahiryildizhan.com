<?php

use Illuminate\Support\Facades\Http;

it('requires authentication to view the dashboard', function (): void {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('denies non-admin users from the dashboard', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('dashboard'))->assertForbidden();
});

it('lets an admin view the dashboard', function (): void {
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => []], 200),
        'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 50000]], 200),
        'api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 1]], 200),
    ]);

    $this->actingAs(actingAsAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('admin.dashboard');
});
