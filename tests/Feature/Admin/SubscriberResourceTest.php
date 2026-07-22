<?php

use App\Models\Subscriber;

it('requires authentication to view the subscribers index', function (): void {
    $this->get(route('admin.subscribers.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.subscribers.index'))->assertForbidden();
});

it('lets an admin view the subscribers index', function (): void {
    $admin = actingAsAdmin();
    Subscriber::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.subscribers.index'))
        ->assertOk()
        ->assertViewIs('admin.subscribers.index');
});

it('lets an admin unsubscribe a subscriber', function (): void {
    $admin = actingAsAdmin();
    $subscriber = Subscriber::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->post(route('admin.subscribers.unsubscribe', $subscriber))
        ->assertRedirect();

    $this->assertDatabaseHas('subscribers', ['id' => $subscriber->id, 'status' => 'unsubscribed']);
});

it('lets an admin destroy a subscriber', function (): void {
    $admin = actingAsAdmin();
    $subscriber = Subscriber::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.subscribers.destroy', $subscriber))
        ->assertRedirect();

    $this->assertDatabaseMissing('subscribers', ['id' => $subscriber->id]);
});
