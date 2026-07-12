<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Route-coverage smoke test — every public GET route must respond without a
 * server error (5xx). Guards against fatal regressions on deploy (X9).
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Parameterless public routes that must render without a server error.
     *
     * @return list<string>
     */
    public static function publicRoutes(): array
    {
        return ['/', '/blog', '/timeline', '/biolink', '/search', '/sitemap.xml', '/ads.txt'];
    }

    public function test_public_routes_have_no_server_error(): void
    {
        foreach (self::publicRoutes() as $uri) {
            $status = $this->get($uri)->status();

            $this->assertLessThan(
                500,
                $status,
                "Route [{$uri}] returned a server error ({$status})."
            );
        }
    }

    public function test_home_page_is_ok(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_unknown_route_returns_branded_404(): void
    {
        $this->get('/this-route-does-not-exist-'.uniqid())
            ->assertNotFound();
    }
}
