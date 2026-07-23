<?php

use App\Models\Consumption;
use App\Models\Food;
use App\Models\TimeRange;
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

it('shows the current time range on the clock ring', function (): void {
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => []], 200),
        'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 50000]], 200),
        'api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 1]], 200),
    ]);

    TimeRange::query()->create([
        'day_of_week' => now()->dayOfWeek,
        'starts_at' => '00:00',
        'ends_at' => '23:59',
        'label' => 'Tüm Gün',
        'color' => '#3B82F6',
    ]);

    $this->actingAs(actingAsAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Tüm Gün');
});

it('shows daily nutrition totals on the dashboard', function (): void {
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => []], 200),
        'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 50000]], 200),
        'api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 1]], 200),
    ]);

    $food = Food::factory()->create(['calories_per_100g' => 200, 'fat_per_100g' => 10]);
    Consumption::factory()->create([
        'food_id' => $food->id,
        'consumed_on' => now()->toDateString(),
        'quantity' => 100,
    ]);

    $this->actingAs(actingAsAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('200 kcal');
});
