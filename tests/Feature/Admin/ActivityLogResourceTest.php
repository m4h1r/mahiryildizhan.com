<?php

use App\Models\ActivityLog;

it('requires authentication to view the activity log', function (): void {
    $this->get(route('admin.activity-log.index'))->assertRedirect(route('login'));
});

it('denies non-admin users from the activity log', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.activity-log.index'))->assertForbidden();
});

it('lets an admin view the activity log', function (): void {
    $admin = actingAsAdmin();
    ActivityLog::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.activity-log.index'))
        ->assertOk()
        ->assertViewIs('admin.activity-logs.index');
});
