<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\MailchimpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscriber::query()->latest('subscribed_at')->latest('id');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where('email', 'like', "%{$search}%");
        }

        return view('admin.subscribers.index', [
            'title' => 'Subscribers',
            'heading' => 'Subscribers',
            'subscribers' => $query->paginate(30)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function export(): StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'status', 'subscribed_at', 'unsubscribed_at', 'mailchimp_id']);

            Subscriber::query()->orderBy('id')->chunk(200, function ($chunk) use ($handle): void {
                foreach ($chunk as $subscriber) {
                    fputcsv($handle, [
                        $subscriber->email,
                        $subscriber->status,
                        optional($subscriber->subscribed_at)->toDateTimeString(),
                        optional($subscriber->unsubscribed_at)->toDateTimeString(),
                        $subscriber->mailchimp_id,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function unsubscribe(Subscriber $subscriber, MailchimpService $mailchimpService): RedirectResponse
    {
        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        if ($subscriber->mailchimp_id) {
            $mailchimpService->unsubscribe((string) $subscriber->mailchimp_id);
        }

        return back()->with('success', 'Subscriber unsubscribed.');
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber deleted.');
    }
}