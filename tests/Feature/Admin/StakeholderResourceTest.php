<?php

use App\Models\Stakeholder;

it('requires authentication to view the stakeholders index', function (): void {
    $this->get(route('admin.stakeholders.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.stakeholders.index'))->assertForbidden();
});

it('lets an admin view the stakeholders index', function (): void {
    $admin = actingAsAdmin();
    Stakeholder::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.stakeholders.index'))
        ->assertOk()
        ->assertViewIs('admin.stakeholders.index');
});

it('lets an admin create a stakeholder', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.stakeholders.store'), [
        'vkn_tckn' => '12345678901',
        'company_type' => 'Individual',
        'status' => 'Active',
    ]);

    $response->assertRedirect(route('admin.stakeholders.index'));
    $this->assertDatabaseHas('stakeholders', ['vkn_tckn' => '12345678901']);
});

it('rejects a duplicate vkn_tckn', function (): void {
    $admin = actingAsAdmin();
    Stakeholder::factory()->create(['vkn_tckn' => '11122233344']);

    $response = $this->actingAs($admin)->post(route('admin.stakeholders.store'), [
        'vkn_tckn' => '11122233344',
        'company_type' => 'Individual',
        'status' => 'Active',
    ]);

    $response->assertSessionHasErrors(['vkn_tckn']);
    $this->assertDatabaseCount('stakeholders', 1);
});

it('lets an admin update a stakeholder', function (): void {
    $admin = actingAsAdmin();
    $stakeholder = Stakeholder::factory()->create(['title' => 'Old Co']);

    $response = $this->actingAs($admin)->put(route('admin.stakeholders.update', $stakeholder), [
        'vkn_tckn' => $stakeholder->vkn_tckn,
        'title' => 'New Co',
        'company_type' => $stakeholder->company_type,
        'status' => $stakeholder->status,
    ]);

    $response->assertRedirect(route('admin.stakeholders.index'));
    $this->assertDatabaseHas('stakeholders', ['id' => $stakeholder->id, 'title' => 'New Co']);
});

it('soft deletes a stakeholder on destroy', function (): void {
    $admin = actingAsAdmin();
    $stakeholder = Stakeholder::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.stakeholders.destroy', $stakeholder))
        ->assertRedirect(route('admin.stakeholders.index'));

    $this->assertSoftDeleted('stakeholders', ['id' => $stakeholder->id]);
});
