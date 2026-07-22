<?php

use App\Models\TimelineEvent;

it('requires authentication to view the timeline index', function (): void {
    $this->get(route('admin.timeline.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.timeline.index'))->assertForbidden();
});

it('lets an admin view the timeline index', function (): void {
    $admin = actingAsAdmin();
    TimelineEvent::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.timeline.index'))
        ->assertOk()
        ->assertViewIs('admin.timeline.index');
});

it('lets an admin create a timeline event', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.timeline.store'), [
        'title' => 'Launched the site',
        'event_type' => 'milestone',
        'start_date' => now()->toDateString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('timeline_events', ['title' => 'Launched the site']);
});

it('rejects an invalid timeline event payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.timeline.store'), [
        'title' => '',
        'event_type' => 'not-a-real-type',
    ]);

    $response->assertSessionHasErrors(['title', 'event_type', 'start_date']);
    $this->assertDatabaseCount('timeline_events', 0);
});

it('lets an admin update a timeline event', function (): void {
    $admin = actingAsAdmin();
    $event = TimelineEvent::factory()->create(['title' => 'Old']);

    $response = $this->actingAs($admin)->put(route('admin.timeline.update', $event), [
        'title' => 'New',
        'event_type' => $event->event_type,
        'start_date' => $event->start_date->toDateString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('timeline_events', ['id' => $event->id, 'title' => 'New']);
});

it('lets an admin destroy a timeline event', function (): void {
    $admin = actingAsAdmin();
    $event = TimelineEvent::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.timeline.destroy', $event))
        ->assertRedirect();

    $this->assertDatabaseMissing('timeline_events', ['id' => $event->id]);
});
