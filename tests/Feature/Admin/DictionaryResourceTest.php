<?php

use App\Models\Gender;

it('requires authentication to view a dictionary table', function (): void {
    $this->get(route('admin.dictionaries.index', 'genders'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())
        ->get(route('admin.dictionaries.index', 'genders'))
        ->assertForbidden();
});

it('404s for an unknown dictionary table', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)
        ->get(route('admin.dictionaries.index', 'not-a-real-table'))
        ->assertNotFound();
});

it('lets an admin view a dictionary table', function (): void {
    $admin = actingAsAdmin();
    Gender::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.dictionaries.index', 'genders'))
        ->assertOk()
        ->assertViewIs('admin.dictionaries.index');
});

it('lets an admin create a dictionary entry', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.dictionaries.store', 'genders'), [
        'name' => 'Non-binary',
    ]);

    $response->assertRedirect(route('admin.dictionaries.index', 'genders'));
    $this->assertDatabaseHas('genders', ['name' => 'Non-binary']);
});

it('rejects an invalid dictionary entry payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.dictionaries.store', 'genders'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['name']);
    $this->assertDatabaseCount('genders', 0);
});

it('lets an admin update a dictionary entry', function (): void {
    $admin = actingAsAdmin();
    $gender = Gender::factory()->create(['name' => 'Old']);

    $response = $this->actingAs($admin)->put(route('admin.dictionaries.update', ['genders', $gender->id]), [
        'name' => 'New',
    ]);

    $response->assertRedirect(route('admin.dictionaries.index', 'genders'));
    $this->assertDatabaseHas('genders', ['id' => $gender->id, 'name' => 'New']);
});

it('lets an admin destroy a dictionary entry', function (): void {
    $admin = actingAsAdmin();
    $gender = Gender::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.dictionaries.destroy', ['genders', $gender->id]))
        ->assertRedirect(route('admin.dictionaries.index', 'genders'));

    $this->assertDatabaseMissing('genders', ['id' => $gender->id]);
});
