<?php

/*
|--------------------------------------------------------------------------
| Indexing registry (E8)
|--------------------------------------------------------------------------
| Every named GET route on the web middleware group declares its indexing
| policy here. A route that is NOT declared and NOT covered by an exempt
| prefix throws in local/staging (so it is caught in development) and falls
| back to `noindex,nofollow` in production while failing the smoke test.
|
| This is the single source of truth for `X-Robots-Tag`; the IndexingPolicy
| middleware applies it as a header (defence in depth alongside the <meta
| robots> rendered by <x-seo-meta>). Adding a page is not "done" until its
| route appears here.
|
| Alice/API routes (routes/alice.php, routes/api.php) run on the `api` group,
| never reach this middleware, and are intentionally absent.
*/

return [
    // Route-name prefixes that are always noindex and need no per-route entry:
    // the admin panel, the dashboard, and every auth screen.
    'exempt_prefixes' => [
        'admin.',
        'horizon',
        'pulse',
        'dashboard',
        'profile',
        'login',
        'register',
        'password',
        'password.',
        'two-factor.',
        'verification.',
        'confirm-password',
        'logout',
        'user-',
    ],

    // Explicit policy per public web route. Values: 'index,follow' or
    // 'noindex,follow' / 'noindex,nofollow'.
    'routes' => [
        // Indexable public content
        'home' => 'index,follow',
        'about' => 'index,follow',
        'biolink' => 'index,follow',
        'blog.index' => 'index,follow',
        'blog.show' => 'index,follow',
        'timeline.public' => 'index,follow',
        'legal.privacy' => 'index,follow',
        'legal.kvkk' => 'index,follow',
        'legal.cookies' => 'index,follow',
        'legal.terms' => 'index,follow',

        // Public but not worth indexing (thin / results / redirect / action)
        'search' => 'noindex,follow',
        'links.show' => 'noindex,follow',
        'sitemap.xml' => 'noindex,follow',
        'robots.txt' => 'noindex,follow',
        'ads.txt' => 'noindex,follow',
        'llms.txt' => 'noindex,follow',
        'llms.txt.en' => 'noindex,follow',
        'locale.switch' => 'noindex,nofollow',
        'theme.switch' => 'noindex,nofollow',
        'public.subscribers.confirm' => 'noindex,nofollow',
    ],

    // Applied to any web route that is neither declared nor exempt.
    'default_unlisted' => 'noindex,nofollow',
];
