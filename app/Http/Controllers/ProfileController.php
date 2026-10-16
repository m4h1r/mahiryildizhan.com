<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\EmailChangeAlertMail;
use App\Mail\PendingEmailVerificationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $twoFactorEnabled = ! is_null($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at);
        $twoFactorPending = ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at);

        return view('profile.edit', [
            'user' => $user,
            'twoFactorEnabled' => $twoFactorEnabled,
            'twoFactorPending' => $twoFactorPending,
            'qrCodeSvg' => $twoFactorPending ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => $twoFactorPending ? decrypt($user->two_factor_secret) : null,
            'recoveryCodes' => $twoFactorEnabled ? $user->recoveryCodes() : [],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $newEmail = $validated['email'] ?? $user->email;
        unset($validated['email']); // e-mail is never written here (AC3)

        $user->fill($validated);
        $user->save();

        // AC3 — an e-mail change is an account transfer. Park the new address,
        // send the verify link to the NEW address and an alert to the OLD one.
        // users.email is only rewritten once the link is confirmed.
        if ($newEmail !== $user->email) {
            $token = Str::random(64);

            $user->forceFill([
                'pending_email' => $newEmail,
                'pending_email_token' => Hash::make($token),
                'pending_email_expires_at' => now()->addMinutes(60),
            ])->save();

            $verificationUrl = route('profile.email.verify', ['token' => $token]);

            Mail::to($newEmail)->send(new PendingEmailVerificationMail($verificationUrl));
            Mail::to($user->email)->send(new EmailChangeAlertMail($newEmail));

            return Redirect::route('profile.edit')->with('status', 'pending-email-sent');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Confirm a pending e-mail change (AC3). Only here does users.email move.
     */
    public function confirmEmailChange(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        $valid = $user->pending_email
            && $user->pending_email_token
            && $user->pending_email_expires_at
            && $user->pending_email_expires_at->isFuture()
            && Hash::check($token, $user->pending_email_token);

        if (! $valid) {
            return Redirect::route('profile.edit')->with('status', 'pending-email-invalid');
        }

        $user->forceFill([
            'email' => $user->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'pending_email_token' => null,
            'pending_email_expires_at' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'email-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
