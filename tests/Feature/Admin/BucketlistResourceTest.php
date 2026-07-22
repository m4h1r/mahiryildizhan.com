<?php

use App\Models\PurchaseItem;
use App\Models\TodoItem;

it('requires authentication to view the bucketlist', function (): void {
    $this->get(route('admin.bucketlist'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.bucketlist'))->assertForbidden();
});

it('lets an admin view the bucketlist with completion percentage', function (): void {
    $admin = actingAsAdmin();
    PurchaseItem::factory()->create(['is_bucketlist' => true, 'is_completed' => true]);
    PurchaseItem::factory()->create(['is_bucketlist' => true, 'is_completed' => false]);
    TodoItem::factory()->create(['is_bucketlist' => true, 'is_completed' => false]);

    $response = $this->actingAs($admin)->get(route('admin.bucketlist'));

    $response->assertOk()->assertViewIs('admin.bucketlist.index');
    expect($response->viewData('total'))->toBe(3);
    expect($response->viewData('completed'))->toBe(1);
});
