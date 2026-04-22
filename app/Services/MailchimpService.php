<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailchimpService
{
    public function subscribe(string $email): array
    {
        $apiKey = $this->apiKey();
        $listId = $this->listId();
        $dataCenter = $this->dataCenter($apiKey);

        if (! $apiKey || ! $listId || ! $dataCenter) {
            Log::warning('Mailchimp credentials missing; subscriber saved locally only.');

            return ['success' => false];
        }

        $response = Http::withBasicAuth('anystring', $apiKey)
            ->acceptJson()
            ->post("https://{$dataCenter}.api.mailchimp.com/3.0/lists/{$listId}/members", [
                'email_address' => $email,
                'status' => 'subscribed',
            ]);

        if (! $response->successful()) {
            Log::warning('Mailchimp subscribe failed.', [
                'email' => $email,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return ['success' => false];
        }

        return [
            'success' => true,
            'member_id' => (string) ($response->json('id') ?? ''),
        ];
    }

    public function unsubscribe(string $mailchimpId): bool
    {
        $apiKey = $this->apiKey();
        $listId = $this->listId();
        $dataCenter = $this->dataCenter($apiKey);

        if (! $apiKey || ! $listId || ! $dataCenter || $mailchimpId === '') {
            return false;
        }

        $response = Http::withBasicAuth('anystring', $apiKey)
            ->acceptJson()
            ->patch("https://{$dataCenter}.api.mailchimp.com/3.0/lists/{$listId}/members/{$mailchimpId}", [
                'status' => 'unsubscribed',
            ]);

        return $response->successful();
    }

    private function apiKey(): ?string
    {
        return config('services.mailchimp.api_key') ?: Setting::get('mailchimp_api_key');
    }

    private function listId(): ?string
    {
        return config('services.mailchimp.list_id') ?: Setting::get('mailchimp_list_id');
    }

    private function dataCenter(?string $apiKey): ?string
    {
        $configured = config('services.mailchimp.datacenter') ?: Setting::get('mailchimp_datacenter');

        if ($configured) {
            return strtolower((string) $configured);
        }

        if (! $apiKey || ! str_contains($apiKey, '-')) {
            return null;
        }

        return strtolower((string) explode('-', $apiKey)[1]);
    }
}