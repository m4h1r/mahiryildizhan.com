<?php

use App\Models\Consumption;
use App\Models\Food;
use App\Models\TimeRange;
use Illuminate\Support\Facades\Http;

function fakeDashboardHttpCalls(): void
{
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => []], 200),
        'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 50000]], 200),
        'api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 1]], 200),
    ]);
}

function extractCalorieBarClass(string $html): string
{
    $labelPos = strpos($html, 'Kalori Hedefi');
    $divPos = strpos($html, '<div class="h-full rounded-full', $labelPos);
    $tagEnd = strpos($html, '>', $divPos);

    return substr($html, $divPos, $tagEnd - $divPos);
}

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

it('shows daily nutrition totals including protein on the dashboard', function (): void {
    fakeDashboardHttpCalls();

    $food = Food::factory()->create([
        'calories_per_100g' => 200,
        'fat_per_100g' => 10,
        'protein_per_100g' => 25,
    ]);
    Consumption::factory()->create([
        'food_id' => $food->id,
        'consumed_on' => now()->toDateString(),
        'quantity' => 100,
    ]);

    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('200 kcal')
        ->assertSee('Protein')
        ->assertSee('25.0 g', false);

    // Protein must actually reach the Chart.js dataset, not just the text lines.
    $start = strrpos($response->getContent(), 'nutritionPieChart');
    $scriptTag = substr($response->getContent(), strrpos(substr($response->getContent(), 0, $start), '<script'));
    expect($scriptTag)->toContain('25');
});

it('shows the macro goals in parentheses next to each daily total', function (): void {
    fakeDashboardHttpCalls();

    $this->actingAs(actingAsAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('(350g)', false)
        ->assertSee('(140g)', false)
        ->assertSee('(70g)', false);
});

it('colors the calorie goal bar green when within the healthy range', function (): void {
    fakeDashboardHttpCalls();

    // 2000 kcal is between 3/4 (1875) and 5/4 (3125) of the 2500 goal.
    $food = Food::factory()->create(['calories_per_100g' => 2000]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'));

    expect(extractCalorieBarClass($response->getContent()))->toContain('bg-green-500');
});

it('colors the calorie goal bar red when calories exceed 5/4 of the goal', function (): void {
    fakeDashboardHttpCalls();

    // 3200 kcal > 3125 (5/4 of 2500).
    $food = Food::factory()->create(['calories_per_100g' => 3200]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'));

    expect(extractCalorieBarClass($response->getContent()))->toContain('bg-red-500');
});

it('colors the calorie goal bar yellow when calories are below 3/4 of the goal', function (): void {
    fakeDashboardHttpCalls();

    // 1000 kcal < 1875 (3/4 of 2500).
    $food = Food::factory()->create(['calories_per_100g' => 1000]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'));

    expect(extractCalorieBarClass($response->getContent()))->toContain('bg-amber-500');
});

it('gives the nutrition chart script a CSP nonce so the browser does not block it', function (): void {
    // Regression: the inline <script> that boots the Chart.js doughnut chart
    // was missing the nonce="" attribute required by the CSP script-src
    // policy (no 'unsafe-inline' — see SecurityHeadersMiddleware). Without
    // it, the browser silently drops the script and the chart never renders.
    Http::fake([
        'api.open-meteo.com/*' => Http::response(['daily' => []], 200),
        'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 50000]], 200),
        'api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 1]], 200),
    ]);

    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'));
    $response->assertOk();

    $html = $response->getContent();
    // Last occurrence of the id is inside the script's own JS (getElementById(...)),
    // so searching backward from there finds the <script> tag that wraps it.
    $start = strrpos($html, 'nutritionPieChart');
    $scriptStart = strrpos(substr($html, 0, $start), '<script');
    $scriptTag = substr($html, $scriptStart, strpos($html, '>', $scriptStart) - $scriptStart);

    expect($scriptTag)->toContain('nonce="')
        ->and($scriptTag)->not->toContain('nonce=""');
});
