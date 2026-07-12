<?php

namespace Tests\Feature;

use App\Models\Adage;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase16CoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_controller_store_creates_published_post_with_generated_slug(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Cagdas Tasarim Notlari',
            'body' => 'This post body contains enough words to calculate reading time correctly.',
            'status' => 'published',
            'published' => 1,
        ]);

        $response->assertRedirect(route('admin.posts.index'));

        $post = Post::query()->first();
        $this->assertNotNull($post);
        $this->assertSame('cagdas-tasarim-notlari', $post->slug);
        $this->assertSame('published', $post->status);
        $this->assertTrue((bool) $post->published);
        $this->assertNotNull($post->published_at);
    }

    public function test_expense_controller_store_calculates_total(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $currency = Currency::query()->create([
            'code' => 'TRY',
            'name' => 'Turkish Lira',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'date' => '2026-03-18',
            'currency_id' => $currency->id,
            'description' => 'Design tooling',
            'price' => 100,
            'quantity' => 2,
            'tax' => 20,
            'company_expense' => 1,
            'paid_by_others' => 0,
        ]);

        $response->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::query()->first();
        $this->assertNotNull($expense);
        $this->assertSame('220.00', (string) $expense->total);
    }

    public function test_csv_import_dry_run_does_not_persist_rows(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $directory = storage_path('app/import');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = $directory.'/adages.csv';
        $backupExists = is_file($path);
        $backup = $backupExists ? file_get_contents($path) : null;

        file_put_contents($path, "adage\nDry run adage\n");

        try {
            $response = $this->actingAs($admin)->post(route('admin.import.run'), [
                'table' => 'adages',
                'dry_run' => 1,
            ]);

            $response->assertRedirect(route('admin.import.index'));
            $this->assertDatabaseMissing('adages', ['adage' => 'Dry run adage']);
            $this->assertSame(0, Adage::query()->count());
        } finally {
            if ($backupExists) {
                file_put_contents($path, (string) $backup);
            } else {
                @unlink($path);
            }
        }
    }

    public function test_csv_importing_same_file_twice_does_not_create_duplicates(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);

        $directory = storage_path('app/import');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = $directory.'/adages.csv';
        $backupExists = is_file($path);
        $backup = $backupExists ? file_get_contents($path) : null;

        file_put_contents($path, "owner,adage\nTest Owner,Idempotent import adage\n");

        try {
            $firstRun = $this->actingAs($admin)->post(route('admin.import.run'), [
                'table' => 'adages',
            ]);

            $firstRun->assertRedirect(route('admin.import.index'));
            $this->assertSame(1, Adage::query()->where('adage', 'Idempotent import adage')->count());

            $secondRun = $this->actingAs($admin)->post(route('admin.import.run'), [
                'table' => 'adages',
            ]);

            $secondRun->assertRedirect(route('admin.import.index'));
            $this->assertSame(1, Adage::query()->where('adage', 'Idempotent import adage')->count());
            $this->assertSame(1, Adage::query()->count());
        } finally {
            if ($backupExists) {
                file_put_contents($path, (string) $backup);
            } else {
                @unlink($path);
            }
        }
    }

    public function test_artisan_import_all_dry_run_does_not_persist_rows(): void
    {
        $directory = storage_path('app/import');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = $directory.'/adages.csv';
        $backupExists = is_file($path);
        $backup = $backupExists ? file_get_contents($path) : null;

        file_put_contents($path, "owner,adage\nDry Run Owner,Dry run all adage\n");

        try {
            $this->artisan('import:csv --all --dry-run');

            $this->assertDatabaseMissing('adages', ['adage' => 'Dry run all adage']);
            $this->assertSame(0, Adage::query()->count());
        } finally {
            if ($backupExists) {
                file_put_contents($path, (string) $backup);
            } else {
                @unlink($path);
            }
        }
    }

    public function test_smoke_get_routes_without_parameters_return_200_or_302(): void
    {
        $admin = User::factory()->create(['id' => 1, 'is_admin' => true]);
        $this->actingAs($admin);

        foreach (Route::getRoutes() as $route) {
            $methods = $route->methods();
            $uri = ltrim($route->uri(), '/');

            if (! in_array('GET', $methods, true) || str_contains($uri, '{')) {
                continue;
            }

            if (str_starts_with($uri, '_') || str_starts_with($uri, 'telescope') || str_starts_with($uri, 'api/')) {
                continue;
            }

            $response = $this->get('/'.$uri);

            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Route [{$uri}] returned [{$response->getStatusCode()}]"
            );
        }
    }
}
