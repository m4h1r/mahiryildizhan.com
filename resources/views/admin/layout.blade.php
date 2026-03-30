<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-default-theme="light" data-theme-storage-key="theme_admin">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __($title ?? 'Admin Panel') }} | {{ config('app.name') }}</title>
        <x-brand-meta />
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            (function () {
                var root = document.documentElement;
                var storageKey = root.dataset.themeStorageKey || 'theme_admin';
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
    </head>
    <body class="min-h-screen bg-[var(--color-admin-bg)] text-gray-900 antialiased dark:bg-[var(--color-admin-bg-dark)] dark:text-gray-100" x-data="{ sidebarOpen: false }">
        @php($dictionaryNavigation = \App\Http\Controllers\Admin\DictionaryController::navigation())

        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            {{ __('Skip to content') }}
        </a>

        <div class="grid min-h-screen lg:grid-cols-[260px_1fr]">
            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full border-r border-gray-200 bg-[#f7f8fb] p-6 transition-transform duration-200 dark:border-gray-800 dark:bg-[#0d0f14] lg:static lg:w-auto lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : ''"
            >
                <a href="{{ route('dashboard') }}" class="mb-8 flex items-center gap-3 text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    <x-application-logo class="h-9 w-9 rounded-lg object-contain" />
                    <span>{{ config('app.name') }}</span>
                </a>

                <nav class="space-y-2 text-sm">
                    <a href="{{ route('dashboard') }}" class="admin-nav-link {{ request()->routeIs('dashboard') ? 'admin-nav-link-active' : '' }}">{{ __('Dashboard') }}</a>
                    <a href="{{ route('admin.posts.index') }}" class="admin-nav-link {{ request()->routeIs('admin.posts.*') ? 'admin-nav-link-active' : '' }}">{{ __('Posts') }}</a>
                    <a href="{{ route('admin.media.index') }}" class="admin-nav-link {{ request()->routeIs('admin.media.*') ? 'admin-nav-link-active' : '' }}">{{ __('Media') }}</a>
                    <a href="{{ route('admin.comments.index') }}" class="admin-nav-link {{ request()->routeIs('admin.comments.*') ? 'admin-nav-link-active' : '' }}">{{ __('Comments') }}</a>
                    <a href="{{ route('admin.people.index') }}" class="admin-nav-link {{ request()->routeIs('admin.people.*') ? 'admin-nav-link-active' : '' }}">{{ __('People') }}</a>
                    <a href="{{ route('admin.interactions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.interactions.*') ? 'admin-nav-link-active' : '' }}">{{ __('Interactions') }}</a>
                    <a href="{{ route('admin.nodes.index') }}" class="admin-nav-link {{ request()->routeIs('admin.nodes.*') ? 'admin-nav-link-active' : '' }}">{{ __('Nodes') }}</a>
                    <a href="{{ route('admin.node-connections.index') }}" class="admin-nav-link {{ request()->routeIs('admin.node-connections.*') ? 'admin-nav-link-active' : '' }}">{{ __('Node Connections') }}</a>
                    <a href="{{ route('admin.timeline.index') }}" class="admin-nav-link {{ request()->routeIs('admin.timeline.*') ? 'admin-nav-link-active' : '' }}">{{ __('Timeline') }}</a>
                    <a href="{{ route('admin.adages.index') }}" class="admin-nav-link {{ request()->routeIs('admin.adages.*') ? 'admin-nav-link-active' : '' }}">{{ __('Adages') }}</a>
                    <a href="{{ route('admin.links.index') }}" class="admin-nav-link {{ request()->routeIs('admin.links.*') ? 'admin-nav-link-active' : '' }}">{{ __('Links') }}</a>
                    <a href="{{ route('admin.subscribers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.subscribers.*') ? 'admin-nav-link-active' : '' }}">{{ __('Subscribers') }}</a>
                    <a href="{{ route('admin.stakeholders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.stakeholders.*') ? 'admin-nav-link-active' : '' }}">{{ __('Stakeholders') }}</a>
                    <a href="{{ route('admin.expenses.index') }}" class="admin-nav-link {{ request()->routeIs('admin.expenses.*') ? 'admin-nav-link-active' : '' }}">{{ __('Expenses') }}</a>
                    <a href="{{ route('admin.incomes.index') }}" class="admin-nav-link {{ request()->routeIs('admin.incomes.*') ? 'admin-nav-link-active' : '' }}">{{ __('Incomes') }}</a>
                    <a href="{{ route('admin.reports') }}" class="admin-nav-link {{ request()->routeIs('admin.reports') ? 'admin-nav-link-active' : '' }}">{{ __('Reports') }}</a>
                    <a href="{{ route('admin.import.index') }}" class="admin-nav-link {{ request()->routeIs('admin.import.*') ? 'admin-nav-link-active' : '' }}">{{ __('Import') }}</a>
                    <a href="{{ route('admin.settings') }}" class="admin-nav-link {{ request()->routeIs('admin.settings') ? 'admin-nav-link-active' : '' }}">{{ __('Settings') }}</a>
                </nav>

                <div class="mt-8">
                    <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ __('Dictionaries') }}</p>

                    <nav class="space-y-2 text-sm">
                        @foreach ($dictionaryNavigation as $dictionaryItem)
                            <a
                                href="{{ route('admin.dictionaries.index', $dictionaryItem['table']) }}"
                                class="admin-nav-link {{ request()->routeIs('admin.dictionaries.*') && request()->route('table') === $dictionaryItem['table'] ? 'admin-nav-link-active' : '' }}"
                            >
                                {{ $dictionaryItem['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="relative">
                <div
                    class="fixed inset-0 z-40 bg-gray-950/35 lg:hidden"
                    x-show="sidebarOpen"
                    x-transition.opacity
                    x-on:click="sidebarOpen = false"
                    x-cloak
                ></div>

                <header class="sticky top-0 z-30 flex items-center justify-between border-b border-gray-200 bg-white/95 px-4 py-3 backdrop-blur md:px-6 md:py-4 dark:border-gray-800 dark:bg-[#0f1117]/95">
                    <h1 class="text-base font-semibold">{{ __($heading ?? 'Admin') }}</h1>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="sidebarOpen = !sidebarOpen"
                            class="admin-btn admin-btn-ghost lg:hidden"
                            aria-label="{{ __('Toggle navigation') }}"
                        >
                            {{ __('Menu') }}
                        </button>

                        <button
                            type="button"
                            data-theme-toggle
                            class="admin-btn admin-btn-ghost"
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

                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="admin-btn admin-btn-ghost text-xs">
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </header>

                <main id="main-content" class="p-4 md:p-6">
                    <x-flash />
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
