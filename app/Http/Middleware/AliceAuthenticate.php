<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AliceAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sanctum token check
        if (! $request->bearerToken()) {
            return $this->unauthorized('Token gereklidir');
        }

        $token = PersonalAccessToken::findToken($request->bearerToken());

        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            return $this->unauthorized('Geçersiz veya süresi dolmuş token');
        }

        $user = $token->tokenable;
        if (! $user || ! $user->is_admin) {
            return $this->unauthorized('Yetkisiz erişim');
        }

        // Update last used
        $token->forceFill(['last_used_at' => now()])->save();

        // IP whitelist check
        $allowed = config('alice.allowed_ips', []);
        if (! empty($allowed) && ! in_array($request->ip(), $allowed)) {
            return response()->json([
                'error' => [
                    'code' => 'ip_not_allowed',
                    'message' => 'Bu IP adresinden erişim izni yok',
                ],
            ], 403);
        }

        // Bind user to request so downstream code can access it
        auth()->setUser($user);

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'error' => [
                'code' => 'unauthenticated',
                'message' => $message,
            ],
        ], 401);
    }
}
