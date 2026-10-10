<?php

use App\Models\TodoItem;
use App\Models\User;

it('assigns created_by automatically from authenticated users and falls back to Alice Hermes', function (): void {
    $admin = actingAsAdmin();
    $alice = User::updateOrCreate(
        ['id' => 2],
        ['name' => 'Alice Hermes', 'email' => 'alice.hermes.__1791661575191638000@local.test', 'password' => bcrypt('password123'), 'is_admin' => false]
    );

    $item = TodoItem::factory()->create();
    expect($item->created_by)->toBe($admin->id);

    auth()->logout();
    $fallbackItem = TodoItem::factory()->create();
    expect($fallbackItem->created_by)->toBe($alice->id);

    if ($admin) {
        auth()->login($admin);
    }
});
