<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        // Avoid a real network call to the pwnedpasswords API (Password::uncompromised()).
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'a-Str0ng-Rand0m-Passw0rd!',
                'password_confirmation' => 'a-Str0ng-Rand0m-Passw0rd!',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('a-Str0ng-Rand0m-Passw0rd!', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'a-Str0ng-Rand0m-Passw0rd!',
                'password_confirmation' => 'a-Str0ng-Rand0m-Passw0rd!',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
}
