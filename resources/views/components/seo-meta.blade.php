@props([
    'post' => null,
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => 'index,follow',
])

@php
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $siteName = Setting::get('site_name', config('app.name'));
    $defaultDescription = Setting::get('default_meta_description', config('app.name'));
    $defaultOgImage = Setting::get('default_og_image');

    $resolveUrl = static function (?string $value): ?string {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $normalized = ltrim($value, '/');

        if (Str::startsWith($normalized, 'storage/') || Str::startsWith($normalized, 'build/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    };

    $isHomeRoute = request()->routeIs('home');
    $isBlogPostRoute = request()->routeIs('blog.show') && $post;
    $postTitle = $post?->seo_title ?: $post?->title;

    $pageTitle = match (true) {
        $isHomeRoute => 'Mahir Yıldızhan',
        $isBlogPostRoute => ($postTitle ? $postTitle.' | MY' : 'MY'),
        default => $title ?: ($postTitle ?: $siteName),
    };
    $blogFallbackDescription = $isBlogPostRoute ? "Mahir Yıldızhan Kişisel Bloğu | {$postTitle} hakkında görüşleri." : null;
    $metaDescription = $description ?: ($post?->seo_description ?: $post?->excerpt ?: ($blogFallbackDescription ?: $defaultDescription));
    $canonicalUrl = $canonical ?: ($post?->canonical_url ?: url()->current());
    $ogImageUrl = $resolveUrl($post?->og_image ?: $post?->cover_url ?: $post?->cover ?: $defaultOgImage);
    $schemaType = $post?->schema_type ?: 'WebPage';
    $home = rtrim(url('/'), '/').'/';
    $orgId = $home.'#organization';
    $siteId = $home.'#website';

    // E10 — Organization + WebSite shipped on every page; per-type node + a
    // BreadcrumbList describing the real path. Nothing is marked up that the
    // page does not actually show.
    $organization = [
        '@type' => 'Organization',
        '@id' => $orgId,
        'name' => $siteName,
        'url' => $home,
    ];
    $logoUrl = $resolveUrl($defaultOgImage) ?: asset('logo.png');
    if ($logoUrl) {
        $organization['logo'] = $logoUrl;
    }

    $website = [
        '@type' => 'WebSite',
        '@id' => $siteId,
        'name' => $siteName,
        'url' => $home,
        'inLanguage' => str_replace('_', '-', app()->getLocale()),
        'publisher' => ['@id' => $orgId],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('search').'?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $pageNode = [
        '@type' => $schemaType,
        '@id' => $canonicalUrl.'#'.($post ? 'article' : 'webpage'),
        'headline' => $post?->title ?: $pageTitle,
        'name' => $pageTitle,
        'description' => $metaDescription,
        'url' => $canonicalUrl,
        'isPartOf' => ['@id' => $siteId],
        'inLanguage' => str_replace('_', '-', app()->getLocale()),
    ];

    if ($ogImageUrl) {
        $pageNode['image'] = [$ogImageUrl];
    }
    if ($post?->published_at) {
        $pageNode['datePublished'] = $post->published_at->toAtomString();
    }
    if ($post?->updated_at) {
        $pageNode['dateModified'] = $post->updated_at->toAtomString();
    }
    if ($post?->author?->name) {
        $pageNode['author'] = ['@type' => 'Person', 'name' => $post->author->name];
    }

    // BreadcrumbList — Home, then the current section/post.
    $crumbs = [[
        '@type' => 'ListItem',
        'position' => 1,
        'name' => $siteName,
        'item' => $home,
    ]];
    if (! $isHomeRoute) {
        if ($isBlogPostRoute) {
            $crumbs[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('Stories'),
                'item' => route('blog.index'),
            ];
            $crumbs[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $postTitle ?: $pageTitle,
                'item' => $canonicalUrl,
            ];
        } else {
            $crumbs[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $pageTitle,
                'item' => $canonicalUrl,
            ];
        }
    }
    $breadcrumb = [
        '@type' => 'BreadcrumbList',
        '@id' => $canonicalUrl.'#breadcrumb',
        'itemListElement' => $crumbs,
    ];

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [$organization, $website, $pageNode, $breadcrumb],
    ];
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:type" content="{{ $post ? 'article' : 'website' }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">

@if ($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">

@if ($post?->seo_keywords)
    <meta name="keywords" content="{{ $post->seo_keywords }}">
@endif

<script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce', '') }}">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>