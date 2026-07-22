<?php

use App\Models\Media;

it('requires authentication to view the media index', function (): void {
    $this->get(route('admin.media.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.media.index'))->assertForbidden();
});

it('lets an admin view the media index', function (): void {
    $admin = actingAsAdmin();
    Media::query()->create([
        'filename' => 'photo.jpg',
        'path' => 'media/photo.jpg',
        'disk' => 'public',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
        'type' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertViewIs('admin.media.index');
});

it('lets an admin destroy an unused media item', function (): void {
    $admin = actingAsAdmin();
    $media = Media::query()->create([
        'filename' => 'photo.jpg',
        'path' => 'media/photo.jpg',
        'disk' => 'public',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
        'type' => 1,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.media.destroy', $media))
        ->assertRedirect(route('admin.media.index'));

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});
