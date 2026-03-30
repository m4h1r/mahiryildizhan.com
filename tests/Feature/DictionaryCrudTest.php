<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DictionaryCrudTest extends TestCase
{
    use RefreshDatabase;

    private const TABLE_PAYLOADS = [
        'genders' => [
            'create' => ['name' => 'Test Gender', 'slug' => ''],
            'update' => ['name' => 'Updated Gender', 'slug' => 'updated-gender'],
            'unique_key' => 'name',
        ],
        'eye_colors' => [
            'create' => ['name' => 'Test Eye Color', 'slug' => ''],
            'update' => ['name' => 'Updated Eye Color', 'slug' => 'updated-eye-color'],
            'unique_key' => 'name',
        ],
        'blood_types' => [
            'create' => ['name' => 'Test Blood Type'],
            'update' => ['name' => 'Updated Blood Type'],
            'unique_key' => 'name',
        ],
        'hair_colors' => [
            'create' => ['name' => 'Test Hair Color', 'slug' => ''],
            'update' => ['name' => 'Updated Hair Color', 'slug' => 'updated-hair-color'],
            'unique_key' => 'name',
        ],
        'post_categories' => [
            'create' => ['name' => 'Test Category', 'slug' => '', 'description' => 'Initial'],
            'update' => ['name' => 'Updated Category', 'slug' => 'updated-category', 'description' => 'Updated'],
            'unique_key' => 'name',
        ],
        'post_languages' => [
            'create' => ['name' => 'QA Language One', 'code' => 'qa1'],
            'update' => ['name' => 'QA Language Two', 'code' => 'qa2'],
            'unique_key' => 'code',
        ],
        'income_sources' => [
            'create' => ['name' => 'Test Source'],
            'update' => ['name' => 'Updated Source'],
            'unique_key' => 'name',
        ],
        'income_types' => [
            'create' => ['name' => 'Test Type'],
            'update' => ['name' => 'Updated Type'],
            'unique_key' => 'name',
        ],
        'currencies' => [
            'create' => ['code' => 'try', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            'update' => ['code' => 'usd', 'name' => 'US Dollar', 'symbol' => '$'],
            'unique_key' => 'code',
        ],
        'expense_types' => [
            'create' => ['name' => 'Test Expense', 'government_acceptance_percentage' => 100],
            'update' => ['name' => 'Updated Expense', 'government_acceptance_percentage' => 65],
            'unique_key' => 'name',
        ],
        'interaction_types' => [
            'create' => ['name' => 'Test Interaction'],
            'update' => ['name' => 'Updated Interaction'],
            'unique_key' => 'name',
        ],
    ];

    public function test_dictionary_index_pages_are_accessible_for_all_tables(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (array_keys(self::TABLE_PAYLOADS) as $table) {
            $response = $this->actingAs($admin)->get(route('admin.dictionaries.index', $table));
            $response->assertOk();
        }
    }

    public function test_dictionary_crud_works_for_all_tables(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (self::TABLE_PAYLOADS as $table => $config) {
            $createPayload = $config['create'];
            $updatePayload = $config['update'];
            $uniqueKey = $config['unique_key'];

            $createResponse = $this->actingAs($admin)->post(route('admin.dictionaries.store', $table), $createPayload);
            $createResponse->assertRedirect(route('admin.dictionaries.index', $table));

            $createdUniqueValue = in_array($table, ['currencies', 'post_languages'], true)
                ? strtoupper((string) $createPayload[$uniqueKey])
                : $createPayload[$uniqueKey];

            $created = DB::table($table)->where($uniqueKey, $createdUniqueValue)->first();
            $this->assertNotNull($created, "Failed to create dictionary row for [{$table}].");

            $updateResponse = $this->actingAs($admin)->put(
                route('admin.dictionaries.update', [$table, $created->id]),
                $updatePayload
            );
            $updateResponse->assertRedirect(route('admin.dictionaries.index', $table));

            $updatedUniqueValue = in_array($table, ['currencies', 'post_languages'], true)
                ? strtoupper((string) $updatePayload[$uniqueKey])
                : $updatePayload[$uniqueKey];

            $this->assertNotNull(
                DB::table($table)->where('id', $created->id)->where($uniqueKey, $updatedUniqueValue)->first(),
                "Failed to update dictionary row for [{$table}]."
            );

            $deleteResponse = $this->actingAs($admin)->delete(route('admin.dictionaries.destroy', [$table, $created->id]));
            $deleteResponse->assertRedirect(route('admin.dictionaries.index', $table));

            $this->assertNull(
                DB::table($table)->where('id', $created->id)->first(),
                "Failed to delete dictionary row for [{$table}]."
            );
        }
    }
}
