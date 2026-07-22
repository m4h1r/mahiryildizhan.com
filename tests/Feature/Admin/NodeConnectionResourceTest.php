<?php

use App\Models\Node;
use App\Models\NodeConnection;

it('requires authentication to view the node connections index', function (): void {
    $this->get(route('admin.node-connections.index'))->assertRedirect(route('login'));
});

it('denies non-admin users', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.node-connections.index'))->assertForbidden();
});

it('lets an admin view the node connections index', function (): void {
    $admin = actingAsAdmin();
    NodeConnection::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.node-connections.index'))
        ->assertOk()
        ->assertViewIs('admin.node-connections.index');
});

it('lets an admin create a node connection', function (): void {
    $admin = actingAsAdmin();
    $from = Node::factory()->create();
    $to = Node::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.node-connections.store'), [
        'node_from_id' => $from->id,
        'node_to_id' => $to->id,
    ]);

    $response->assertRedirect(route('admin.node-connections.index'));
    $this->assertDatabaseHas('node_connections', ['node_from_id' => $from->id, 'node_to_id' => $to->id]);
});

it('rejects an invalid node connection payload', function (): void {
    $admin = actingAsAdmin();

    $response = $this->actingAs($admin)->post(route('admin.node-connections.store'), [
        'node_from_id' => 999999,
    ]);

    $response->assertSessionHasErrors(['node_from_id']);
    $this->assertDatabaseCount('node_connections', 0);
});

it('lets an admin destroy a node connection', function (): void {
    $admin = actingAsAdmin();
    $connection = NodeConnection::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.node-connections.destroy', $connection))
        ->assertRedirect(route('admin.node-connections.index'));

    $this->assertDatabaseMissing('node_connections', ['id' => $connection->id]);
});
