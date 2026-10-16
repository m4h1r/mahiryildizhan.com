<?php

/*
|--------------------------------------------------------------------------
| CTA policy (V31)
|--------------------------------------------------------------------------
| The site has ONE primary conversion action, recorded in project-standards.md:
|   Primary : subscribe to the newsletter  (public.subscribers.store form)
|   Support : secondary nudges (read more, about)
|
| Every public web route declares its relationship to that action here. Unlike
| the indexing registry (E8), a missing entry is a ⚠️ (missed opportunity), not
| a build failure. Legal/account/action routes are always 'none'.
*/

return [
    'primary_action' => 'subscribe',

    'routes' => [
        'home' => 'primary',   // the subscribe block lives here
        'blog.index' => 'support',
        'blog.show' => 'primary',   // subscribe prompt after a post
        'about' => 'support',
        'biolink' => 'support',
        'timeline.public' => 'support',
        'search' => 'none',
        'legal.privacy' => 'none',
        'legal.kvkk' => 'none',
        'legal.cookies' => 'none',
        'legal.terms' => 'none',
        'links.show' => 'none',
        'locale.switch' => 'none',
        'theme.switch' => 'none',
        'public.subscribers.confirm' => 'none',
        'sitemap.xml' => 'none',
        'robots.txt' => 'none',
        'ads.txt' => 'none',
        'llms.txt' => 'none',
        'llms.txt.en' => 'none',
    ],

    'default_unlisted' => 'none',
];
