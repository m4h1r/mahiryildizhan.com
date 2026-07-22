<?php

use App\Models\Comment;

it('requires authentication to view the comments index', function (): void {
    $this->get(route('admin.comments.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.comments.index'))->assertForbidden();
});

it('lets an admin view the comments index', function (): void {
    $admin = actingAsAdmin();
    Comment::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.comments.index'))
        ->assertOk()
        ->assertViewIs('admin.comments.index');
});

it('lets an admin approve a comment', function (): void {
    $admin = actingAsAdmin();
    $comment = Comment::factory()->create(['is_approved' => false]);

    $this->actingAs($admin)
        ->put(route('admin.comments.approve', $comment))
        ->assertRedirect();

    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'is_approved' => true]);
});

it('soft deletes a comment on destroy', function (): void {
    $admin = actingAsAdmin();
    $comment = Comment::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.comments.destroy', $comment))
        ->assertRedirect();

    $this->assertSoftDeleted('comments', ['id' => $comment->id]);
});
