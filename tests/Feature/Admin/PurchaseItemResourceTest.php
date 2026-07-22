<?php

use App\Models\PurchaseItem;

it('requires authentication to view the purchase items index', function (): void {
    $this->get(route('admin.purchase-items.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.purchase-items.index'))->assertForbidden();
});

it('lets an admin view the purchase items index', function (): void {
    $admin = actingAsAdmin();
    PurchaseItem::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.purchase-items.index'))
        ->assertOk()
        ->assertViewIs('admin.purchase-items.index');
});

it('lets an admin create a purchase item', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.purchase-items.store'), [
        'title' => 'New Laptop',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('purchase_items', ['title' => 'New Laptop']);
});

it('rejects an invalid purchase item payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.purchase-items.store'), [
        'title' => '',
    ]);

    $response->assertSessionHasErrors(['title']);
    $this->assertDatabaseCount('purchase_items', 0);
});

it('lets an admin toggle purchase item completion', function (): void {
    $admin = actingAsAdmin();
    $item = PurchaseItem::factory()->create(['is_completed' => false]);

    $this->actingAs($admin)
        ->patch(route('admin.purchase-items.toggle-complete', $item))
        ->assertRedirect();

    $this->assertDatabaseHas('purchase_items', ['id' => $item->id, 'is_completed' => true]);
});

it('soft deletes a purchase item on destroy', function (): void {
    $admin = actingAsAdmin();
    $item = PurchaseItem::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.purchase-items.destroy', $item))
        ->assertRedirect();

    $this->assertSoftDeleted('purchase_items', ['id' => $item->id]);
});
