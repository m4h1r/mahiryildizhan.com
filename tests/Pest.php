<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function actingAsAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function actingAsNonAdmin(): User
{
    // User ID 1 is treated as an implicit admin by EnsureAdmin — create a
    // throwaway user first so the returned user never lands on ID 1.
    User::factory()->create();

    return User::factory()->create(['is_admin' => false]);
}
