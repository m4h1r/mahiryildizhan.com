<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(?string $token, ?string $ip = null): array
    {
        $secret = config('services.recaptcha.secret') ?: Setting::get('recaptcha_secret_key');

        if (! $secret) {
            return [
                'success' => true,
                'score' => null,
                'errors' => [],
                'skipped' => true,
            ];
        }

        if (! $token) {
            return [
                'success' => false,
                'score' => 0.0,
                'errors' => ['missing-input-response'],
                'skipped' => false,
            ];
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (! $response->ok()) {
            return [
                'success' => false,
                'score' => 0.0,
                'errors' => ['recaptcha-http-error'],
                'skipped' => false,
            ];
        }

        $payload = $response->json();

        return [
            'success' => (bool) ($payload['success'] ?? false),
            'score' => isset($payload['score']) ? (float) $payload['score'] : null,
            'errors' => array_values($payload['error-codes'] ?? []),
            'skipped' => false,
        ];
    }
}
