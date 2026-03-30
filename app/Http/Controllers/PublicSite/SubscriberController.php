<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\MailchimpService;
use Illuminate\Http\RedirectResponse;

class SubscriberController extends Controller
{
    public function store(\Illuminate\Http\Request $request, MailchimpService $mailchimpService): RedirectResponse
    {
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