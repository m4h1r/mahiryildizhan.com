<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the indexing registry (E8) as an X-Robots-Tag header on web GET
 * routes. A route that is neither declared in config/seo.php nor matched by an
 * exempt prefix is a bug: it throws in local/staging so it is caught before
 * ship, and falls back to noindex,nofollow in production (with a header that
 * the smoke test asserts against) so an undecided page is never silently
 * indexed.
 */
class IndexingPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only GET/HEAD responses can be indexed.
        if (! $request->isMethodSafe()) {
            return $response;
        }

        $name = $request->route()?->getName();
        $policy = $this->resolvePolicy($name);

        $response->headers->set('X-Robots-Tag', $policy);

        return $response;
    }

    private function resolvePolicy(?string $name): string
    {
        $config = config('seo');
        $default = $config['default_unlisted'] ?? 'noindex,nofollow';

        // Unnamed routes (fallbacks, 404s, package routes) cannot be registered;
        // never throw, just keep them out of the index.
        if ($name === null) {
            return $default;
        }

        foreach ($config['exempt_prefixes'] ?? [] as $prefix) {
            if ($name === $prefix || str_starts_with($name, $prefix)) {
                return 'noindex,nofollow';
            }
        }

        if (isset($config['routes'][$name])) {
            return $config['routes'][$name];
        }

        // A NAMED but undeclared route is a real omission — surface it in
        // development, fail closed in production.
        if (app()->environment('local', 'staging')) {
            throw new RuntimeException(
                "Route [{$name}] has no indexing policy. Declare it in config/seo.php (E8)."
            );
        }

        return $default;
    }
}
