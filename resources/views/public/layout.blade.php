<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-default-theme="light" data-theme-storage-key="theme_public">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-seo-meta
            :post="$seoPost ?? ($post ?? null)"
            :title="$title ?? null"
            :description="$description ?? null"
            :canonical="$canonical ?? null"
            :robots="$robots ?? 'index,follow'"
        />
        <x-brand-meta />
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            (function () {
                var root = document.documentElement;
                var storageKey = root.dataset.themeStorageKey || 'theme_public';
                var defaultTheme = root.dataset.defaultTheme || 'light';
                var savedTheme = null;

                try {
                    savedTheme = localStorage.getItem(storageKey);
                } catch (e) {
                    savedTheme = null;
                }

                var isDark = savedTheme === 'dark' || (savedTheme !== 'light' && defaultTheme === 'dark');
                root.classList.toggle('dark', isDark);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-brand-vars />
        <x-consent-defaults />
        <x-adsense-head />
        <x-gtm-head />
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' });
                });
            }
        </script>
    </head>
    <body class="min-h-screen bg-[var(--color-surface)] text-gray-900 antialiased dark:bg-[var(--color-surface-dark)] dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            Skip to content
        </a>

        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="public-subtle-grid absolute inset-0 opacity-50 dark:opacity-20"></div>
            <div class="absolute -top-40 left-1/2 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-cyan-400/15 blur-3xl dark:bg-cyan-500/10"></div>
            <div class="absolute bottom-[-8rem] right-[-5rem] h-72 w-72 rounded-full bg-orange-300/20 blur-3xl dark:bg-orange-400/10"></div>
        </div>

        <header class="public-glass sticky top-0 z-40 border-b">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-9 w-9 rounded-lg object-contain" />
                    <span class="public-editorial-heading hidden text-xl font-semibold tracking-tight text-[#1d1d1f] md:inline dark:text-gray-100">{{ config('app.name') }}</span>
                </a>

                <div class="flex items-center gap-2 md:gap-3">
                    <a href="{{ route('home') }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold tracking-wide {{ request()->routeIs('home') ? 'border-[#1d1d1f] bg-[#1d1d1f] text-white dark:border-gray-100 dark:bg-gray-100 dark:text-gray-900' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}">{{ __('Home') }}</a>
                    <a href="{{ route('blog.index') }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold tracking-wide {{ request()->routeIs('blog.*') ? 'border-[#1d1d1f] bg-[#1d1d1f] text-white dark:border-gray-100 dark:bg-gray-100 dark:text-gray-900' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}">{{ __('Stories') }}</a>
                    <a href="{{ route('about') }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold tracking-wide {{ request()->routeIs('about') ? 'border-[#1d1d1f] bg-[#1d1d1f] text-white dark:border-gray-100 dark:bg-gray-100 dark:text-gray-900' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}">{{ __('About') }}</a>
                    <button
                        type="button"
                        data-theme-toggle
                        class="inline-flex items-center justify-center rounded-full border border-gray-300 p-2 text-gray-700 dark:border-gray-700 dark:text-gray-200"
                        aria-label="{{ __('Toggle theme') }}"
                    >
                        <span class="items-center justify-center" title="{{ __('Dark') }}" data-theme-icon="dark">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
                            </svg>
                        </span>
                        <span class="hidden items-center justify-center" title="{{ __('Light') }}" data-theme-icon="light">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 3-8 3 8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14h4"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m8-9h-2M6 12H4m13.657 5.657-1.414-1.414M7.757 7.757 6.343 6.343m11.314 0-1.414 1.414M7.757 16.243l-1.414 1.414"/>
                            </svg>
                        </span>
                        <span class="sr-only">{{ __('Toggle theme') }}</span>
                    </button>
                </div>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-6xl px-4 py-12 md:px-6 md:py-16">
            <x-flash />
            @yield('content')

            @if (request()->routeIs('blog.*'))
                <div class="mt-12">
                    <x-ad-unit />
                </div>
            @endif
        </main>

        <footer class="public-glass border-t">
            <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-3 px-4 py-6 text-xs text-gray-500 md:flex-row md:items-center">
                <p>© {{ now()->year }} {{ config('app.name') }}. {{ __('Designed for reading.') }}</p>

                <a
                    href="{{ route('login') }}"
                    class="rounded-full border border-gray-300 px-4 py-2 text-xs font-semibold tracking-[0.08em] text-gray-700 transition hover:border-gray-500 hover:text-gray-900 dark:border-gray-700 dark:text-gray-200"
                >
                    {{ __('Admin Panel') }}
                </a>
            </div>
        </footer>
        <x-footer-credit />
        <x-cookie-consent />
    </body>
</html>
