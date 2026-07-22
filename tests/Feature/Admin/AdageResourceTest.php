<?php

use App\Models\Adage;

it('requires authentication to view the adages index', function (): void {
    $this->get(route('admin.adages.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.adages.index'))->assertForbidden();
});

it('lets an admin view the adages index', function (): void {
    $admin = actingAsAdmin();
    Adage::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.adages.index'))
        ->assertOk()
        ->assertViewIs('admin.adages.index');
});

it('lets an admin create an adage', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.adages.store'), [
        'owner' => 'Confucius',
        'adage' => 'It does not matter how slowly you go as long as you do not stop.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('adages', ['owner' => 'Confucius']);
});

it('rejects an invalid adage payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.adages.store'), [
        'owner' => '',
    ]);

    $response->assertSessionHasErrors(['owner', 'adage']);
    $this->assertDatabaseCount('adages', 0);
});

it('lets an admin update an adage', function (): void {
    $admin = actingAsAdmin();
    $adage = Adage::factory()->create(['owner' => 'Old Owner']);

    $response = $this->actingAs($admin)->put(route('admin.adages.update', $adage), [
        'owner' => 'New Owner',
        'adage' => $adage->adage,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('adages', ['id' => $adage->id, 'owner' => 'New Owner']);
});

it('lets an admin destroy an adage', function (): void {
    $admin = actingAsAdmin();
    $adage = Adage::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.adages.destroy', $adage))
        ->assertRedirect();

    $this->assertDatabaseMissing('adages', ['id' => $adage->id]);
});
