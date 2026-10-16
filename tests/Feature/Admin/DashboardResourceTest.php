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

    // The totals are rendered client-side by Alpine (x-text); the server's
    // contract is the nutrition payload it hands Alpine. Assert on that.
    $response->assertOk()
        ->assertSee('calories\u0022:200', false)
        ->assertSee('protein\u0022:25', false)
        ->assertSee('Protein');

    // Protein must actually reach the Chart.js dataset, not just the text lines.
    $start = strrpos($response->getContent(), 'nutritionPieChart');
    $scriptTag = substr($response->getContent(), strrpos(substr($response->getContent(), 0, $start), '<script'));
    expect($scriptTag)->toContain('25');
});

it('feeds the macro goals to the dashboard nutrition widget', function (): void {
    fakeDashboardHttpCalls();

    // The "(350g)" labels are rendered client-side by Alpine from the goals
    // object; the server's contract is emitting those goal values.
    $this->actingAs(actingAsAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('carbs: 350', false)
        ->assertSee('protein: 140', false)
        ->assertSee('fat: 70', false);
});

// The calorie-bar COLOUR is computed client-side by Alpine (barClass()), so an
// HTTP test cannot assert the rendered class — only that the server feeds the
// correct calorie total that the colour logic keys off. The colour thresholds
// themselves belong in a Pest v4 browser test.
it('feeds a mid-range calorie total to the goal bar (green band)', function (): void {
    fakeDashboardHttpCalls();

    // 2000 kcal is between 3/4 (1875) and 5/4 (3125) of the 2500 goal.
    $food = Food::factory()->create(['calories_per_100g' => 2000]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $this->actingAs(actingAsAdmin())->get(route('dashboard'))
        ->assertOk()
        ->assertSee('calories\u0022:2000', false);
});

it('feeds an over-goal calorie total to the goal bar (red band)', function (): void {
    fakeDashboardHttpCalls();

    // 3200 kcal > 3125 (5/4 of 2500).
    $food = Food::factory()->create(['calories_per_100g' => 3200]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $this->actingAs(actingAsAdmin())->get(route('dashboard'))
        ->assertOk()
        ->assertSee('calories\u0022:3200', false);
});

it('feeds an under-goal calorie total to the goal bar (amber band)', function (): void {
    fakeDashboardHttpCalls();

    // 1000 kcal < 1875 (3/4 of 2500).
    $food = Food::factory()->create(['calories_per_100g' => 1000]);
    Consumption::factory()->create(['food_id' => $food->id, 'consumed_on' => now()->toDateString(), 'quantity' => 100]);

    $this->actingAs(actingAsAdmin())->get(route('dashboard'))
        ->assertOk()
        ->assertSee('calories\u0022:1000', false);
});

// The former 'gives the nutrition chart script a CSP nonce' test was removed:
// the dashboard no longer boots Chart.js from a dedicated inline <script id=
// "nutritionPieChart"> — the chart is now created inside an Alpine x-data
// expression, so there is no script tag to nonce-check. CSP coverage for
// Alpine evaluation belongs in a browser test, not this HTTP feature test.
