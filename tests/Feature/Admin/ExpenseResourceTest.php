<?php

use App\Models\Currency;
use App\Models\Expense;

it('requires authentication to view the expenses index', function (): void {
    $this->get(route('admin.expenses.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.expenses.index'))->assertForbidden();
});

it('lets an admin view the expenses index', function (): void {
    $admin = actingAsAdmin();
    Expense::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.expenses.index'))
        ->assertOk()
        ->assertViewIs('admin.expenses.index');
});

it('lets an admin create an expense', function (): void {
    $admin = actingAsAdmin();
    $currency = Currency::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
        'date' => now()->toDateString(),
        'currency_id' => $currency->id,
        'price' => 100,
        'quantity' => 1,
    ]);

    $response->assertRedirect(route('admin.expenses.index'));
    $this->assertDatabaseHas('expenses', ['currency_id' => $currency->id]);
});

it('rejects an invalid expense payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.expenses.store'), [
        'date' => '',
        'price' => -5,
    ]);

    $response->assertSessionHasErrors(['date', 'currency_id', 'price']);
    $this->assertDatabaseCount('expenses', 0);
});

it('lets an admin update an expense', function (): void {
    $admin = actingAsAdmin();
    $expense = Expense::factory()->create(['price' => 10]);

    $response = $this->actingAs($admin)->put(route('admin.expenses.update', $expense), [
        'date' => $expense->date->toDateString(),
        'currency_id' => $expense->currency_id,
        'price' => 250,
        'quantity' => 1,
    ]);

    $response->assertRedirect(route('admin.expenses.index'));
    $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'price' => 250]);
});

it('soft deletes an expense on destroy', function (): void {
    $admin = actingAsAdmin();
    $expense = Expense::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.expenses.destroy', $expense))
        ->assertRedirect(route('admin.expenses.index'));

    $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
});
