<?php

namespace Tests\Feature\Alice;

use App\Models\Consumption;
use App\Models\Food;

class ConsumptionCrudTest extends AliceTestCase
{
    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/alice/consumptions')->assertStatus(401);
    }

    public function test_index_returns_paginated_consumptions(): void
    {
        Consumption::factory()->create();

        $this->aliceGet('consumptions')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_creates_consumption_with_food_id(): void
    {
        $food = Food::factory()->create([
            'calories_per_100g' => 200,
            'carbs_per_100g' => 20,
            'sugar_per_100g' => 5,
            'fat_per_100g' => 10,
        ]);

        $response = $this->alicePost('consumptions', [
            'food_id' => $food->id,
            'consumed_on' => '2026-07-23',
            'quantity' => 150,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.food_id', $food->id)
            ->assertJsonPath('data.calories', 300)
            ->assertJsonPath('data.carbs', 30)
            ->assertJsonPath('data.fat', 15);

        $this->assertDatabaseHas('consumptions', ['food_id' => $food->id]);
    }

    public function test_store_resolves_food_by_name(): void
    {
        $food = Food::factory()->create(['name' => 'Yulaf Ezmesi']);

        $response = $this->alicePost('consumptions', [
            'food' => 'Yulaf',
            'consumed_on' => '2026-07-23',
            'quantity' => 100,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.food_id', $food->id);
    }

    public function test_store_returns_404_when_food_name_not_found(): void
    {
        $response = $this->alicePost('consumptions', [
            'food' => 'Var Olmayan Besin',
            'consumed_on' => '2026-07-23',
            'quantity' => 100,
        ]);

        $response->assertStatus(404)->assertJsonPath('error.code', 'not_found');
        $this->assertDatabaseCount('consumptions', 0);
    }

    public function test_store_validation_fails_without_food_reference(): void
    {
        $this->alicePost('consumptions', ['consumed_on' => '2026-07-23', 'quantity' => 100])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_show_returns_consumption(): void
    {
        $consumption = Consumption::factory()->create();

        $this->aliceGet("consumptions/{$consumption->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $consumption->id);
    }

    public function test_show_returns_404_for_missing_consumption(): void
    {
        $this->aliceGet('consumptions/99999')->assertStatus(404);
    }

    public function test_update_patches_consumption(): void
    {
        $consumption = Consumption::factory()->create(['quantity' => 50]);

        $this->alicePatch("consumptions/{$consumption->id}", ['quantity' => 200])
            ->assertStatus(200)
            ->assertJsonPath('data.quantity', '200.00');
    }

    public function test_destroy_soft_deletes_consumption(): void
    {
        $consumption = Consumption::factory()->create();

        $this->aliceDelete("consumptions/{$consumption->id}")->assertStatus(200);

        $this->assertSoftDeleted('consumptions', ['id' => $consumption->id]);
    }

    public function test_filter_by_date_range(): void
    {
        Consumption::factory()->create(['consumed_on' => '2026-01-01']);
        Consumption::factory()->create(['consumed_on' => '2026-06-15']);

        $response = $this->aliceGet('consumptions', ['from' => '2026-06-01', 'to' => '2026-06-30']);

        $response->assertStatus(200)->assertJsonPath('meta.total', 1);
    }

    public function test_calories_reflect_piece_based_food(): void
    {
        $food = Food::factory()->create([
            'calories_per_100g' => 155,
            'fat_per_100g' => 11,
            'unit_type' => 'piece',
            'grams_per_unit' => 50,
        ]);

        $response = $this->alicePost('consumptions', [
            'food_id' => $food->id,
            'consumed_on' => '2026-07-23',
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.calories', 155)
            ->assertJsonPath('data.fat', 11)
            ->assertJsonPath('data.unit', 'adet');
    }
}
