<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AliceIdempotency
{
    private const WRITE_METHODS = ['POST', 'PATCH', 'PUT', 'DELETE'];

    private const TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), self::WRITE_METHODS)) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');
        if (! $key) {
            return $next($request);
        }

        // Check existing key
        $existing = IdempotencyKey::where('key', $key)->first();

        if ($existing && ! $existing->isExpired()) {
            return response()->json($existing->response_body, $existing->status_code)
                ->header('X-Idempotent-Replayed', 'true');
        }

        // Delete expired entry if exists
        if ($existing) {
            $existing->delete();
        }

        // Attach key to request for AliceAudit middleware
        $request->attributes->set('idempotency_key', $key);

        $response = $next($request);

        // Store response for future replays (only on success)
        if ($response->getStatusCode() < 500) {
            $body = json_decode($response->getContent(), true);
            IdempotencyKey::create([
                'key' => $key,
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'response_body' => $body ?? [],
                'expires_at' => now()->addHours(self::TTL_HOURS),
            ]);
        }

        return $response;
    }
}
