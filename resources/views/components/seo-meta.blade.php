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
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => $schemaType,
        'headline' => $post?->title ?: $pageTitle,
        'description' => $metaDescription,
        'url' => $canonicalUrl,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
        ],
    ];

    if ($ogImageUrl) {
        $jsonLd['image'] = [$ogImageUrl];
    }

    if ($post?->published_at) {
        $jsonLd['datePublished'] = $post->published_at->toAtomString();
    }

    if ($post?->updated_at) {
        $jsonLd['dateModified'] = $post->updated_at->toAtomString();
    }

    if ($post?->author?->name) {
        $jsonLd['author'] = [
            '@type' => 'Person',
            'name' => $post->author->name,
        ];
    }
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