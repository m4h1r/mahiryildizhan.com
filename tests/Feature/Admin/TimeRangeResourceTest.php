<?php

use App\Models\TimeRange;

it('requires authentication to sync time ranges', function (): void {
    $this->post(route('admin.time-ranges.sync', 1), ['ranges' => []])
        ->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())
        ->post(route('admin.time-ranges.sync', 1), ['ranges' => []])
        ->assertForbidden();
});

it('lets an admin replace the ranges for a day of week', function (): void {
    $admin = actingAsAdmin();
    TimeRange::query()->create([
        'day_of_week' => 1,
        'starts_at' => '00:00',
        'ends_at' => '06:00',
        'label' => 'Eski',
        'color' => '#000000',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.time-ranges.sync', 1), [
        'ranges' => [
            ['starts_at' => '00:00', 'ends_at' => '07:00', 'label' => 'Uyku', 'color' => '#3B82F6'],
            ['starts_at' => '07:00', 'ends_at' => '19:00', 'label' => 'Çalışma', 'color' => '#F97316'],
        ],
    ]);

    $response->assertRedirect(route('admin.settings', ['tab' => 'time_ranges']));
    $this->assertDatabaseMissing('time_ranges', ['label' => 'Eski']);
    $this->assertDatabaseHas('time_ranges', ['day_of_week' => 1, 'label' => 'Uyku', 'color' => '#3B82F6']);
    $this->assertDatabaseHas('time_ranges', ['day_of_week' => 1, 'label' => 'Çalışma', 'color' => '#F97316']);
    expect(TimeRange::query()->where('day_of_week', 1)->count())->toBe(2);
});

it('rejects an invalid time range payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.time-ranges.sync', 1), [
        'ranges' => [
            ['starts_at' => '00:00', 'ends_at' => '07:00', 'label' => 'Uyku', 'color' => 'not-a-color'],
        ],
    ]);

    $response->assertSessionHasErrors(['ranges.0.color']);
    $this->assertDatabaseCount('time_ranges', 0);
});

it('rejects an out-of-range day of week', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)
        ->post(route('admin.time-ranges.sync', 9), ['ranges' => []])
        ->assertNotFound();
});
