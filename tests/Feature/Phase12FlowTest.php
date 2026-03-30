<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Income;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase12FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_summary_widgets(): void
    {
        Http::fake([
            'https://api.open-meteo.com/*' => Http::response([
                'daily' => [
                    'time' => [now()->toDateString()],
                    'temperature_2m_max' => [20],
                    'temperature_2m_min' => [10],
                ],
            ]),
            'https://api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 100000], 'ethereum' => ['usd' => 5000]]),
            'https://api.exchangerate-api.com/*' => Http::response(['rates' => ['USD' => 0.03, 'EUR' => 0.028, 'GBP' => 0.024]]),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();

        Post::query()->create([
            'title' => 'Published',
            'slug' => 'published',
            'body' => 'Body',
            'user_id' => $author->id,
            'status' => 'published',
            'published' => true,
            'published_at' => now(),
        ]);

        Comment::query()->create([
            'post_id' => 1,
            'guest_name' => 'Guest',
            'body' => 'Pending',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee(__('Published Posts'))
            ->assertSee(__('Pending Comments'))
            ->assertSee(__('Monthly Net'));
    }

    public function test_reports_support_multiple_year_filters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $currency = Currency::query()->create(['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => 'TRY']);
        $type = ExpenseType::query()->create(['name' => 'Infrastructure']);

        Income::query()->create([
            'date' => '2025-06-15',
            'currency_id' => $currency->id,
            'amount' => 1000,
        ]);

        Income::query()->create([
            'date' => '2026-01-20',
            'currency_id' => $currency->id,
            'amount' => 2000,
        ]);

        Expense::query()->create([
            'date' => '2025-06-16',
            'expense_type_id' => $type->id,
            'currency_id' => $currency->id,
            'price' => 400,
            'quantity' => 1,
            'tax' => 40,
            'total' => 440,
            'company_expense' => true,
            'paid_by_others' => false,
        ]);

        Expense::query()->create([
            'date' => '2026-01-21',
            'expense_type_id' => $type->id,
            'currency_id' => $currency->id,
            'price' => 500,
            'quantity' => 1,
            'tax' => 50,
            'total' => 550,
            'company_expense' => false,
            'paid_by_others' => false,
        ]);

        $response2025 = $this->actingAs($admin)->get(route('admin.reports', ['year' => 2025]));
        $response2026 = $this->actingAs($admin)->get(route('admin.reports', ['year' => 2026]));

        $response2025->assertOk()->assertSee('1,000.00');
        $response2026->assertOk()->assertSee('2,000.00');
    }
}