<?php

use App\Models\Consumption;
use App\Models\Food;

it('requires authentication to view the foods index', function (): void {
    $this->get(route('admin.foods.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.foods.index'))->assertForbidden();
});

it('lets an admin view the foods index', function (): void {
    $admin = actingAsAdmin();
    Food::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.foods.index'))
        ->assertOk()
        ->assertViewIs('admin.foods.index');
});

it('lets an admin create a gram-based food', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.foods.store'), [
        'name' => 'Yulaf Ezmesi',
        'calories_per_100g' => 389,
        'carbs_per_100g' => 66,
        'sugar_per_100g' => 1,
        'fat_per_100g' => 7,
        'unit_type' => 'gram',
        'vitamins' => ['vitamin_b1' => 0.76, 'iron' => 4.7],
    ]);

    $response->assertRedirect(route('admin.foods.index'));
    $this->assertDatabaseHas('foods', ['name' => 'Yulaf Ezmesi', 'calories_per_100g' => 389]);
    $food = Food::query()->where('name', 'Yulaf Ezmesi')->firstOrFail();
    expect($food->vitamins)->toBe(['vitamin_b1' => 0.76, 'iron' => 4.7]);
});

it('requires grams_per_unit when unit_type is piece', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.foods.store'), [
        'name' => 'Yumurta',
        'calories_per_100g' => 155,
        'carbs_per_100g' => 1.1,
        'sugar_per_100g' => 1.1,
        'fat_per_100g' => 11,
        'unit_type' => 'piece',
    ]);

    $response->assertSessionHasErrors(['grams_per_unit']);
    $this->assertDatabaseCount('foods', 0);
});

it('rejects an invalid food payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.foods.store'), [
        'name' => '',
        'calories_per_100g' => -5,
    ]);

    $response->assertSessionHasErrors(['name', 'calories_per_100g']);
    $this->assertDatabaseCount('foods', 0);
});

it('lets an admin update a food', function (): void {
    $admin = actingAsAdmin();
    $food = Food::factory()->create(['calories_per_100g' => 100]);

    $response = $this->actingAs($admin)->put(route('admin.foods.update', $food), [
        'name' => $food->name,
        'calories_per_100g' => 250,
        'carbs_per_100g' => 10,
        'sugar_per_100g' => 5,
        'fat_per_100g' => 3,
        'unit_type' => 'gram',
    ]);

    $response->assertRedirect(route('admin.foods.index'));
    $this->assertDatabaseHas('foods', ['id' => $food->id, 'calories_per_100g' => 250]);
});

it('deletes a food with no consumption history', function (): void {
    $admin = actingAsAdmin();
    $food = Food::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.foods.destroy', $food))
        ->assertRedirect(route('admin.foods.index'));

    $this->assertDatabaseMissing('foods', ['id' => $food->id]);
});

it('refuses to delete a food referenced by consumption records', function (): void {
    $admin = actingAsAdmin();
    $food = Food::factory()->create();
    Consumption::factory()->create(['food_id' => $food->id]);

    $this->actingAs($admin)
        ->delete(route('admin.foods.destroy', $food))
        ->assertRedirect(route('admin.foods.index'));

    $this->assertDatabaseHas('foods', ['id' => $food->id]);
});
