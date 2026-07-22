<?php

use App\Models\Post;

it('requires authentication to view the posts index', function (): void {
    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.posts.index'))->assertForbidden();
});

it('lets an admin view the posts index', function (): void {
    $admin = actingAsAdmin();
    Post::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.posts.index'))
        ->assertOk()
        ->assertViewIs('admin.posts.index');
});

it('lets an admin create a post', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
        'title' => 'Hello World',
        'body' => 'Post body content.',
        'status' => 'draft',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('posts', ['title' => 'Hello World']);
});

it('rejects an invalid post payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
        'title' => '',
        'status' => 'not-a-real-status',
    ]);

    $response->assertSessionHasErrors(['title', 'body', 'status']);
    $this->assertDatabaseCount('posts', 0);
});

it('lets an admin update a post', function (): void {
    $admin = actingAsAdmin();
    $post = Post::factory()->create(['title' => 'Old Title']);

    $response = $this->actingAs($admin)->put(route('admin.posts.update', $post), [
        'title' => 'New Title',
        'body' => $post->body,
        'status' => $post->status,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'New Title']);
});

it('soft deletes a post on destroy', function (): void {
    $admin = actingAsAdmin();
    $post = Post::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.posts.destroy', $post))
        ->assertRedirect();

    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});
