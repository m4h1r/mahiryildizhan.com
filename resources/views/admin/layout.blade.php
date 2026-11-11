<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-default-theme="light" data-theme-storage-key="theme_admin" data-rail="0">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __($title ?? 'Admin Panel') }} | {{ config('app.name') }}</title>
        <x-brand-meta />
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            (function () {
                var root = document.documentElement;
                var storageKey = root.dataset.themeStorageKey || 'theme_admin';
                var defaultTheme = root.dataset.defaultTheme || 'light';
                var savedTheme = null;
                var rail = '0';

                try {
                    savedTheme = localStorage.getItem(storageKey);
                    rail = localStorage.getItem('admin_sidebar_collapsed') === '1' ? '1' : '0';
                } catch (e) {
                    savedTheme = null;
                }

                var isDark = savedTheme === 'dark' || (savedTheme !== 'light' && defaultTheme === 'dark');
                root.classList.toggle('dark', isDark);
                root.dataset.rail = rail;
            })();
        </script>
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
        <x-brand-vars />
        @stack('head')
    </head>
    <body class="min-h-screen antialiased" x-data="adminShell()">
        @php $dictionaryNavigation = \App\Http\Controllers\Admin\DictionaryController::navigation(); @endphp

        <div class="glass-canvas" aria-hidden="true"></div>

        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-xl focus:bg-white focus:p-3 focus:text-black">
            {{ __('Skip to content') }}
        </a>

        {{-- Backdrop for the mobile drawer / tablet overlay --}}
        <div class="shell-backdrop" x-show="drawerOpen" x-transition.opacity x-on:click="drawerOpen = false" x-cloak></div>

        {{-- ====================================================
             SIDEBAR — floating condensation pane
        ==================================================== --}}
        <aside
            id="admin-sidebar"
            x-ref="aside"
            class="shell-aside glass condensation"
            aria-label="{{ __('Main navigation') }}"
        >
            <div class="brand-row flex h-16 shrink-0 items-center gap-3 px-4">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 text-[15px] font-semibold text-ink" data-tip="{{ config('app.name') }}">
                    <x-application-logo class="h-9 w-9 shrink-0 rounded-xl object-contain" />
                    <span class="brand-name truncate">{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="admin-scrollbar flex-1 overflow-x-hidden overflow-y-auto px-3 pb-4 pt-1">
                @include('admin.partials.nav-groups')
            </nav>

            <div class="shrink-0 border-t border-glass p-3">
                <button
                    type="button"
                    class="admin-nav-link hidden w-full lg:flex"
                    @click="toggleNav()"
                    :aria-expanded="navExpanded.toString()"
                    aria-controls="admin-sidebar"
                    data-tip="{{ __('Expand menu') }}"
                    title="{{ __('Shortcut') }}: ["
                >
                    <svg class="h-[18px] w-[18px] transition-transform duration-200" :class="collapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="3"/><path stroke-linecap="round" d="M9 4v16"/><path stroke-linecap="round" stroke-linejoin="round" d="m15 10-2 2 2 2"/></svg>
                    <span class="nav-label" x-text="collapsed ? @js(__('Expand menu')) : @js(__('Collapse menu'))">{{ __('Collapse menu') }}</span>
                </button>
            </div>
        </aside>

        {{-- ====================================================
             MAIN AREA
        ==================================================== --}}
        <div class="shell-main">
            <header class="shell-header glass flex h-14 shrink-0 items-center gap-2 px-2 sm:px-3">
                <button
                    type="button"
                    @click="toggleNav()"
                    class="admin-btn admin-btn-ghost h-10 w-10 shrink-0 rounded-xl p-0 lg:hidden"
                    :aria-expanded="navExpanded.toString()"
                    aria-controls="admin-sidebar"
                    aria-label="{{ __('Toggle navigation') }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h16"/>
                    </svg>
                </button>

                <h1 class="min-w-0 flex-1 truncate px-1 text-[15px] font-semibold text-ink sm:text-base">{{ __($heading ?? 'Admin') }}</h1>

                <div class="flex shrink-0 items-center gap-1.5">
                    @stack('header-actions')

                    <button
                        type="button"
                        data-theme-toggle
                        class="admin-btn admin-btn-ghost h-10 w-10 rounded-xl p-0"
                        aria-label="{{ __('Toggle theme') }}"
                    >
                        <span class="flex items-center justify-center" title="{{ __('Dark') }}" data-theme-icon="dark">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
                            </svg>
                        </span>
                        <span class="hidden items-center justify-center" title="{{ __('Light') }}" data-theme-icon="light">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                            </svg>
                        </span>
                        <span class="sr-only">{{ __('Toggle theme') }}</span>
                    </button>

                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn-ghost h-10 w-10 rounded-xl p-0 sm:w-auto sm:px-3" aria-label="{{ __('Logout') }}">
                                <svg class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                                <span class="hidden text-xs sm:inline">{{ __('Logout') }}</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </header>

            <main id="main-content" class="min-w-0 flex-1 pt-4 sm:pt-5 lg:pt-6">
                <x-flash />
                @yield('content')
            </main>

            <div class="mt-6">
                <x-footer-credit />
            </div>
        </div>

        {{-- ====================================================
             MOBILE BOTTOM NAVIGATION
        ==================================================== --}}
        @php
            $bottomNav = [
                ['route' => 'dashboard', 'active' => 'dashboard', 'label' => __('Home'), 'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'],
                ['route' => 'admin.posts.index', 'active' => 'admin.posts.*', 'label' => __('Posts'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM8 7h8M8 11h8M8 15h5"/>'],
                ['route' => 'admin.people.index', 'active' => 'admin.people.*', 'label' => __('People'), 'icon' => '<circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/>'],
                ['route' => 'admin.expenses.index', 'active' => 'admin.expenses.*', 'label' => __('Finance'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 7v1m0-9V4m0 16v-1"/>'],
            ];
        @endphp
        <nav class="shell-bottom-nav glass-strong flex items-center px-1.5 md:hidden" aria-label="{{ __('Quick navigation') }}">
            @foreach ($bottomNav as $item)
                <a href="{{ route($item['route']) }}" class="shell-bottom-link" @if (request()->routeIs($item['active'])) aria-current="page" @endif>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    <span class="max-w-full truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
            <button
                type="button"
                @click="toggleNav()"
                class="shell-bottom-link"
                :aria-expanded="drawerOpen.toString()"
                aria-controls="admin-sidebar"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>{{ __('More') }}</span>
            </button>
        </nav>

        <x-app-dialog />
        @stack('scripts')
    </body>
</html>
