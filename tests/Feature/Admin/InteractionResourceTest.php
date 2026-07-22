<?php

use App\Models\Interaction;
use App\Models\Person;

it('requires authentication to view the interactions index', function (): void {
    $this->get(route('admin.interactions.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.interactions.index'))->assertForbidden();
});

it('lets an admin view the interactions index', function (): void {
    $admin = actingAsAdmin();
    Interaction::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.interactions.index'))
        ->assertOk()
        ->assertViewIs('admin.interactions.index');
});

it('lets an admin create an interaction', function (): void {
    $admin = actingAsAdmin();
    $person = Person::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.interactions.store'), [
        'person_id' => $person->id,
        'date' => now()->toDateString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('interactions', ['person_id' => $person->id]);
});

it('rejects an invalid interaction payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.interactions.store'), [
        'person_id' => '',
        'date' => '',
    ]);

    $response->assertSessionHasErrors(['person_id', 'date']);
    $this->assertDatabaseCount('interactions', 0);
});

it('lets an admin update an interaction', function (): void {
    $admin = actingAsAdmin();
    $interaction = Interaction::factory()->create(['effect' => 'Old']);

    $response = $this->actingAs($admin)->put(route('admin.interactions.update', $interaction), [
        'person_id' => $interaction->person_id,
        'date' => $interaction->date->toDateString(),
        'effect' => 'New',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('interactions', ['id' => $interaction->id, 'effect' => 'New']);
});

it('soft deletes an interaction on destroy', function (): void {
    $admin = actingAsAdmin();
    $interaction = Interaction::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.interactions.destroy', $interaction))
        ->assertRedirect();

    $this->assertSoftDeleted('interactions', ['id' => $interaction->id]);
});
