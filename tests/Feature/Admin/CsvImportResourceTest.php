<?php

it('requires authentication to view the import page', function (): void {
    $this->get(route('admin.import.index'))->assertRedirect(route('login'));
});

it('denies non-admin users from the import page', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.import.index'))->assertForbidden();
});

it('lets an admin view the import page', function (): void {
    $this->actingAs(actingAsAdmin())
        ->get(route('admin.import.index'))
        ->assertOk()
        ->assertViewIs('admin.import');
});

it('requires selecting a table or import-all', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)
        ->post(route('admin.import.run'), [])
        ->assertSessionHasErrors(['table']);
});
