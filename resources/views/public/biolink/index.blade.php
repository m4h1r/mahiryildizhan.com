@extends('public.layout')

@section('content')
    <div class="mx-auto max-w-4xl space-y-8 py-6">

        {{-- Profile Card --}}
        <div class="public-fade-up flex flex-col items-center rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] px-10 py-14 text-center shadow-sm dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
            <img
                src="{{ asset('icons/android-chrome-192x192.png') }}"
                alt="{{ config('app.name') }}"
                class="h-24 w-24 rounded-full border-4 border-white shadow-md dark:border-gray-800"
            >
            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100">{{ config('app.name') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $social->get('social_tagline', __('Software Developer · Writer · Turkey')) }}</p>

            {{-- Social Links --}}
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @if ($social->get('social_github'))
                <a href="{{ $social->get('social_github') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.757-1.333-1.757-1.09-.745.083-.729.083-.729 1.205.085 1.84 1.237 1.84 1.237 1.07 1.834 2.807 1.304 3.492.997.108-.775.418-1.305.762-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.468-2.38 1.235-3.22-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.3 1.23a11.52 11.52 0 0 1 3.003-.404c1.02.005 2.047.138 3.006.404 2.29-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.12 3.176.77.84 1.232 1.91 1.232 3.22 0 4.61-2.807 5.625-5.48 5.92.43.372.823 1.102.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .322.216.694.825.576C20.565 21.796 24 17.298 24 12c0-6.63-5.37-12-12-12z"/></svg>
                    GitHub
                </a>
                @endif
                @if ($social->get('social_youtube'))
                <a href="{{ $social->get('social_youtube') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-red-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-red-500">
                    <svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </a>
                @endif
                @if ($social->get('social_linkedin'))
                <a href="{{ $social->get('social_linkedin') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-blue-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-blue-500">
                    <svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
                @endif
                @if ($social->get('social_instagram'))
                <a href="{{ $social->get('social_instagram') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-pink-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-pink-500">
                    <svg class="h-4 w-4 text-pink-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                    Instagram
                </a>
                @endif
                @if ($social->get('social_twitter'))
                <a href="{{ $social->get('social_twitter') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X (Twitter)
                </a>
                @endif
            </div>
        </div>

        {{-- File / Download Links --}}
        @if ($links->isNotEmpty())
            <div class="space-y-3">
                <p class="px-1 text-xs font-semibold uppercase tracking-[0.15em] text-gray-400">{{ __('Links & Downloads') }}</p>
                @foreach ($links as $link)
                    <a href="{{ route('links.show', $link->slug) }}"
                       class="public-card flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0-3-3m3 3 3-3m-9 5h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-5l-2-2H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-[#1d1d1f] dark:text-gray-100">{{ $link->original_name }}</p>
                                <p class="text-xs text-gray-500">/{{ $link->slug }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ number_format($link->download_count) }} {{ __('downloads') }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Recent Blog Posts --}}
        @if ($posts->isNotEmpty())
            <div>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($posts as $post)
                        <a href="{{ route('blog.show', $post->slug) }}"
                           class="public-card group flex flex-col overflow-hidden p-0">
                            @if ($post->cover_url)
                                <div class="h-40 w-full overflow-hidden">
                                    <img
                                        src="{{ $post->cover_url }}"
                                        alt="{{ $post->title }}"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    >
                                </div>
                            @else
                                <div class="flex h-24 w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 20.25h18A.75.75 0 0 0 21.75 19.5V4.5A.75.75 0 0 0 21 3.75H3A.75.75 0 0 0 2.25 4.5v15a.75.75 0 0 0 .75.75Z"/></svg>
                                </div>
                            @endif
                            <div class="flex flex-col gap-1.5 p-4">
                                <p class="text-sm font-semibold leading-snug text-[#1d1d1f] group-hover:underline dark:text-gray-100">{{ $post->title }}</p>
                                <p class="text-xs text-gray-400">{{ $post->published_at?->format('M j, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection

