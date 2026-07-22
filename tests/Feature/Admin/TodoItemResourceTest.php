<?php

use App\Models\TodoItem;

it('requires authentication to view the todo items index', function (): void {
    $this->get(route('admin.todo-items.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.todo-items.index'))->assertForbidden();
});

it('lets an admin view the todo items index', function (): void {
    $admin = actingAsAdmin();
    TodoItem::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.todo-items.index'))
        ->assertOk()
        ->assertViewIs('admin.todo-items.index');
});

it('lets an admin create a todo item', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.todo-items.store'), [
        'title' => 'Learn Laravel',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('todo_items', ['title' => 'Learn Laravel']);
});

it('rejects an invalid todo item payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.todo-items.store'), [
        'title' => '',
    ]);

    $response->assertSessionHasErrors(['title']);
    $this->assertDatabaseCount('todo_items', 0);
});

it('lets an admin toggle todo item completion', function (): void {
    $admin = actingAsAdmin();
    $item = TodoItem::factory()->create(['is_completed' => false]);

    $this->actingAs($admin)
        ->patch(route('admin.todo-items.toggle-complete', $item))
        ->assertRedirect();

    $this->assertDatabaseHas('todo_items', ['id' => $item->id, 'is_completed' => true]);
});

it('soft deletes a todo item on destroy', function (): void {
    $admin = actingAsAdmin();
    $item = TodoItem::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.todo-items.destroy', $item))
        ->assertRedirect();

    $this->assertSoftDeleted('todo_items', ['id' => $item->id]);
});
