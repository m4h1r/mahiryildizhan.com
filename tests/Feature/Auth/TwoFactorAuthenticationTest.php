<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function otpFor(User $user): string
    {
        $secret = decrypt($user->fresh()->two_factor_secret);

        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    public function test_user_can_enable_and_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'));
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at, 'Should await confirmation.');

        $this->actingAs($user)->post(route('two-factor.confirm'), [
            'code' => $this->otpFor($user),
        ]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertNotEmpty($user->fresh()->recoveryCodes());
    }

    public function test_login_without_two_factor_goes_straight_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_two_factor_is_challenged_then_completed(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $this->actingAs($user)->post(route('two-factor.enable'));
        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $this->otpFor($user)]);
        auth()->logout();

        // Correct password must NOT log in — it must redirect to the challenge.
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        // Clear Fortify's anti-replay cache so the (same-window) enrollment code
        // can be reused here — in production, enrolment and login are separate windows.
        Cache::flush();

        // Valid TOTP completes the login.
        $this->post(route('two-factor.login.store'), ['code' => $this->otpFor($user)])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_completes_the_challenge(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $this->actingAs($user)->post(route('two-factor.enable'));
        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $this->otpFor($user)]);
        $recoveryCode = $user->fresh()->recoveryCodes()[0];
        auth()->logout();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->post(route('two-factor.login.store'), ['recovery_code' => $recoveryCode])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.enable'));
        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $this->otpFor($user)]);

        $this->actingAs($user)->delete(route('two-factor.disable'));

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }
}
