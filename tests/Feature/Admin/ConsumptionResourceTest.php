<?php

use App\Models\Consumption;
use App\Models\Food;

it('requires authentication to view the consumptions index', function (): void {
    $this->get(route('admin.consumptions.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.consumptions.index'))->assertForbidden();
});

it('lets an admin view the consumptions index', function (): void {
    $admin = actingAsAdmin();
    Consumption::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.consumptions.index'))
        ->assertOk()
        ->assertViewIs('admin.consumptions.index');
});

it('lets an admin log a consumption', function (): void {
    $admin = actingAsAdmin();
    $food = Food::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.consumptions.store'), [
        'food_id' => $food->id,
        'consumed_on' => now()->toDateString(),
        'quantity' => 150,
    ]);

    $response->assertRedirect(route('admin.consumptions.index'));
    $this->assertDatabaseHas('consumptions', ['food_id' => $food->id, 'quantity' => 150]);
});

it('rejects an invalid consumption payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.consumptions.store'), [
        'food_id' => 99999,
        'quantity' => -1,
    ]);

    $response->assertSessionHasErrors(['food_id', 'consumed_on', 'quantity']);
    $this->assertDatabaseCount('consumptions', 0);
});

it('lets an admin update a consumption', function (): void {
    $admin = actingAsAdmin();
    $consumption = Consumption::factory()->create(['quantity' => 50]);

    $response = $this->actingAs($admin)->put(route('admin.consumptions.update', $consumption), [
        'food_id' => $consumption->food_id,
        'consumed_on' => $consumption->consumed_on->toDateString(),
        'quantity' => 200,
    ]);

    $response->assertRedirect(route('admin.consumptions.index'));
    $this->assertDatabaseHas('consumptions', ['id' => $consumption->id, 'quantity' => 200]);
});

it('deletes a consumption', function (): void {
    $admin = actingAsAdmin();
    $consumption = Consumption::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.consumptions.destroy', $consumption))
        ->assertRedirect(route('admin.consumptions.index'));

    $this->assertDatabaseMissing('consumptions', ['id' => $consumption->id]);
});

it('computes calories from grams-based food correctly', function (): void {
    $food = Food::factory()->create([
        'calories_per_100g' => 200,
        'carbs_per_100g' => 20,
        'sugar_per_100g' => 5,
        'fat_per_100g' => 10,
        'unit_type' => 'gram',
    ]);
    $consumption = Consumption::factory()->create(['food_id' => $food->id, 'quantity' => 150]);

    expect($consumption->calories())->toBe(300.0)
        ->and($consumption->carbs())->toBe(30.0)
        ->and($consumption->fat())->toBe(15.0);
});

it('computes calories from piece-based food correctly', function (): void {
    $food = Food::factory()->create([
        'calories_per_100g' => 155,
        'carbs_per_100g' => 1.1,
        'sugar_per_100g' => 1.1,
        'fat_per_100g' => 11,
        'unit_type' => 'piece',
        'grams_per_unit' => 50,
    ]);
    $consumption = Consumption::factory()->create(['food_id' => $food->id, 'quantity' => 2]);

    // 2 eggs * 50g = 100g -> exactly the per-100g values
    expect($consumption->calories())->toBe(155.0)
        ->and($consumption->fat())->toBe(11.0);
});
