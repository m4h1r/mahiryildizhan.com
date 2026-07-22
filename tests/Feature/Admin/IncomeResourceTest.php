<?php

use App\Models\Currency;
use App\Models\Income;

it('requires authentication to view the incomes index', function (): void {
    $this->get(route('admin.incomes.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.incomes.index'))->assertForbidden();
});

it('lets an admin view the incomes index', function (): void {
    $admin = actingAsAdmin();
    Income::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.incomes.index'))
        ->assertOk()
        ->assertViewIs('admin.incomes.index');
});

it('lets an admin create an income', function (): void {
    $admin = actingAsAdmin();
    $currency = Currency::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.incomes.store'), [
        'date' => now()->toDateString(),
        'currency_id' => $currency->id,
        'amount' => 500,
    ]);

    $response->assertRedirect(route('admin.incomes.index'));
    $this->assertDatabaseHas('incomes', ['currency_id' => $currency->id, 'amount' => 500]);
});

it('rejects an invalid income payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.incomes.store'), [
        'date' => '',
        'amount' => -1,
    ]);

    $response->assertSessionHasErrors(['date', 'currency_id', 'amount']);
    $this->assertDatabaseCount('incomes', 0);
});

it('lets an admin update an income', function (): void {
    $admin = actingAsAdmin();
    $income = Income::factory()->create(['amount' => 10]);

    $response = $this->actingAs($admin)->put(route('admin.incomes.update', $income), [
        'date' => $income->date->toDateString(),
        'currency_id' => $income->currency_id,
        'amount' => 999,
    ]);

    $response->assertRedirect(route('admin.incomes.index'));
    $this->assertDatabaseHas('incomes', ['id' => $income->id, 'amount' => 999]);
});

it('soft deletes an income on destroy', function (): void {
    $admin = actingAsAdmin();
    $income = Income::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.incomes.destroy', $income))
        ->assertRedirect(route('admin.incomes.index'));

    $this->assertSoftDeleted('incomes', ['id' => $income->id]);
});
