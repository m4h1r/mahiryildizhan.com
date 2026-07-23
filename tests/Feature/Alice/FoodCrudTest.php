<?php

namespace Tests\Feature\Alice;

use App\Models\Food;

class FoodCrudTest extends AliceTestCase
{
    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/alice/foods')->assertStatus(401);
    }

    public function test_index_returns_paginated_foods(): void
    {
        Food::factory()->create(['name' => 'Yulaf Ezmesi']);

        $this->aliceGet('foods')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_creates_gram_based_food(): void
    {
        $response = $this->alicePost('foods', [
            'name' => 'Yulaf Ezmesi',
            'calories_per_100g' => 389,
            'carbs_per_100g' => 66,
            'sugar_per_100g' => 1,
            'fat_per_100g' => 7,
            'unit_type' => 'gram',
            'vitamins' => ['vitamin_b1' => 0.76, 'iron' => 4.7],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Yulaf Ezmesi')
            ->assertJsonPath('data.vitamins.vitamin_b1', 0.76);

        $this->assertDatabaseHas('foods', ['name' => 'Yulaf Ezmesi', 'calories_per_100g' => 389]);
    }

    public function test_store_requires_grams_per_unit_for_piece_type(): void
    {
        $response = $this->alicePost('foods', [
            'name' => 'Yumurta',
            'calories_per_100g' => 155,
            'carbs_per_100g' => 1.1,
            'sugar_per_100g' => 1.1,
            'fat_per_100g' => 11,
            'unit_type' => 'piece',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['fields' => ['grams_per_unit']]]);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->alicePost('foods', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_show_returns_food(): void
    {
        $food = Food::factory()->create();

        $this->aliceGet("foods/{$food->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $food->id);
    }

    public function test_show_returns_404_for_missing_food(): void
    {
        $this->aliceGet('foods/99999')->assertStatus(404);
    }

    public function test_update_patches_food(): void
    {
        $food = Food::factory()->create(['calories_per_100g' => 100]);

        $this->alicePatch("foods/{$food->id}", ['calories_per_100g' => 250])
            ->assertStatus(200)
            ->assertJsonPath('data.calories_per_100g', 250);
    }

    public function test_destroy_soft_deletes_food(): void
    {
        $food = Food::factory()->create();

        $this->aliceDelete("foods/{$food->id}")->assertStatus(200);

        $this->assertSoftDeleted('foods', ['id' => $food->id]);
    }

    public function test_search_by_name(): void
    {
        Food::factory()->create(['name' => 'Yulaf Ezmesi']);
        Food::factory()->create(['name' => 'Tavuk Göğsü']);

        $this->aliceGet('foods', ['q' => 'Yulaf'])
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }
}
