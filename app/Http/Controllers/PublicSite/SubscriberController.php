<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\MailchimpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class SubscriberController extends Controller
{
    public function store(\Illuminate\Http\Request $request, MailchimpService $mailchimpService): RedirectResponse
    {
        if ($request->filled('hp_website')) {
            return back()->with('success', 'Subscription successful.');
        }

        $key = 'subscribe:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['email' => __('Too many attempts. Please try again later.')]);
        }
        RateLimiter::hit($key, 3600);

        $payload = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:subscribers,email'],
        ]);

        $subscriber = Subscriber::query()->create([
            'email' => $payload['email'],
            'status' => 'active',
            'subscribed_at' => now(),
        ]);

        $mailchimpResult = $mailchimpService->subscribe($subscriber->email);

        if (($mailchimpResult['success'] ?? false) && ! empty($mailchimpResult['member_id'])) {
            $subscriber->update(['mailchimp_id' => $mailchimpResult['member_id']]);
        }

        return back()->with('success', 'Subscription successful.');
    }
}