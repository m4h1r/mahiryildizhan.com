<?php

use App\Models\Node;

it('requires authentication to view the nodes index', function (): void {
    $this->get(route('admin.nodes.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.nodes.index'))->assertForbidden();
});

it('lets an admin view the nodes index', function (): void {
    $admin = actingAsAdmin();
    Node::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.nodes.index'))
        ->assertOk()
        ->assertViewIs('admin.nodes.index');
});

it('lets an admin create a node', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.nodes.store'), [
        'name' => 'Concept A',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('nodes', ['name' => 'Concept A']);
});

it('rejects a duplicate node name', function (): void {
    $admin = actingAsAdmin();
    Node::factory()->create(['name' => 'Existing']);

    $response = $this->actingAs($admin)->post(route('admin.nodes.store'), [
        'name' => 'Existing',
    ]);

    $response->assertSessionHasErrors(['name']);
    $this->assertDatabaseCount('nodes', 1);
});

it('lets an admin update a node', function (): void {
    $admin = actingAsAdmin();
    $node = Node::factory()->create(['name' => 'Old']);

    $response = $this->actingAs($admin)->put(route('admin.nodes.update', $node), [
        'name' => 'New',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('nodes', ['id' => $node->id, 'name' => 'New']);
});

it('soft deletes a node on destroy', function (): void {
    $admin = actingAsAdmin();
    $node = Node::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.nodes.destroy', $node))
        ->assertRedirect();

    $this->assertSoftDeleted('nodes', ['id' => $node->id]);
});
