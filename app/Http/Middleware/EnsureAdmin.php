<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $adminEmail = env('ADMIN_EMAIL');
        $isAdminAttribute = (bool) data_get($user, 'is_admin', false);

        if (
            $isAdminAttribute
            || ($adminEmail && strcasecmp((string) $user->email, (string) $adminEmail) === 0)
            || (int) $user->getKey() === 1
        ) {
            return $next($request);
        }

        abort(403, 'This area is restricted to the administrator.');
    }
}
