<?php

use App\Models\Gender;
use App\Models\Person;

it('requires authentication to view the people index', function (): void {
    $this->get(route('admin.people.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.people.index'))->assertForbidden();
});

it('lets an admin view the people index', function (): void {
    $admin = actingAsAdmin();
    Person::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.people.index'))
        ->assertOk()
        ->assertViewIs('admin.people.index');
});

it('lets an admin create a person', function (): void {
    $admin = actingAsAdmin();
    $gender = Gender::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.people.store'), [
        'name' => 'Ada',
        'surname' => 'Lovelace',
        'gender_id' => $gender->id,
    ]);

    $response->assertRedirect(route('admin.people.index'));
    $this->assertDatabaseHas('people', ['name' => 'Ada', 'surname' => 'Lovelace']);
});

it('rejects an invalid person payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.people.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'surname']);
    $this->assertDatabaseCount('people', 0);
});

it('lets an admin update a person', function (): void {
    $admin = actingAsAdmin();
    $person = Person::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put(route('admin.people.update', $person), [
        'name' => 'New Name',
        'surname' => $person->surname,
    ]);

    $response->assertRedirect(route('admin.people.index'));
    $this->assertDatabaseHas('people', ['id' => $person->id, 'name' => 'New Name']);
});

it('soft deletes a person on destroy', function (): void {
    $admin = actingAsAdmin();
    $person = Person::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.people.destroy', $person))
        ->assertRedirect(route('admin.people.index'));

    $this->assertSoftDeleted('people', ['id' => $person->id]);
});
