<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Mail\SubscriberConfirmationMail;
use App\Models\Subscriber;
use App\Services\MailchimpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('hp_website')) {
            return back()->with('success', __('A confirmation email has been sent. Please check your inbox.'));
        }

        $key = 'subscribe:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['email' => __('Too many attempts. Please try again later.')]);
        }
        RateLimiter::hit($key, 3600);

        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $existing = Subscriber::query()->where('email', $payload['email'])->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return back()->with('success', __('You are already subscribed.'));
            }

            if ($existing->status === 'unsubscribed') {
                return back()->withErrors(['email' => __('This email address has been unsubscribed.')]);
            }

            // pending — regenerate token and resend
            $existing->update(['confirmation_token' => Str::random(64)]);
            $existing->refresh();
            Mail::to($existing->email)->send(new SubscriberConfirmationMail($existing, $this->confirmationUrl($existing)));

            return back()->with('success', __('A confirmation email has been sent. Please check your inbox.'));
        }

        $subscriber = Subscriber::query()->create([
            'email' => $payload['email'],
            'status' => 'pending',
            'confirmation_token' => Str::random(64),
        ]);

        Mail::to($subscriber->email)->send(new SubscriberConfirmationMail($subscriber, $this->confirmationUrl($subscriber)));

        return back()->with('success', __('A confirmation email has been sent. Please check your inbox.'));
    }

    public function confirm(Request $request, Subscriber $subscriber): RedirectResponse
    {
        if ($subscriber->status !== 'pending' || $subscriber->confirmation_token !== $request->query('token')) {
            abort(404);
        }

        $subscriber->update([
            'status' => 'active',
            'confirmation_token' => null,
            'subscribed_at' => now(),
            'confirmed_at' => now(),
        ]);

        $mailchimpService = app(MailchimpService::class);
        $result = $mailchimpService->subscribe($subscriber->email);
        if (($result['success'] ?? false) && ! empty($result['member_id'])) {
            $subscriber->update(['mailchimp_id' => $result['member_id']]);
        }

        return redirect()->route('home')->with('success', __('Your subscription has been confirmed. Welcome!'));
    }

    private function confirmationUrl(Subscriber $subscriber): string
    {
        return route('public.subscribers.confirm', [
            'subscriber' => $subscriber->id,
            'token' => $subscriber->confirmation_token,
        ]);
    }
}
