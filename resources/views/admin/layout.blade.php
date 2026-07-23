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
        <x-brand-vars />
    </head>
    <body
        class="min-h-screen bg-[var(--color-admin-bg)] text-gray-900 antialiased dark:bg-[var(--color-admin-bg-dark)] dark:text-gray-100"
        x-data="{ sidebarOpen: false }"
    >
        @php($dictionaryNavigation = \App\Http\Controllers\Admin\DictionaryController::navigation())

        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            {{ __('Skip to content') }}
        </a>

        <div class="grid min-h-screen lg:grid-cols-[260px_1fr]">

            {{-- ====================================================
                 SIDEBAR
            ==================================================== --}}
            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col border-r bg-[var(--color-admin-sidebar)] transition-transform duration-200 ease-out dark:border-[var(--color-admin-border-dark)] dark:bg-[var(--color-admin-sidebar-dark)] lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : ''"
            >
                {{-- Logo --}}
                <div class="flex h-14 shrink-0 items-center gap-3 border-b border-[var(--color-admin-border)] px-5 dark:border-[var(--color-admin-border-dark)]">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-[15px] font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        <x-application-logo class="h-8 w-8 rounded-lg object-contain" />
                        <span>{{ config('app.name') }}</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="admin-scrollbar flex-1 overflow-y-auto px-3 py-4">

                    {{-- Main --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_main') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_main', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Main') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('dashboard') }}" class="admin-nav-link {{ request()->routeIs('dashboard') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('admin.posts.index') }}" class="admin-nav-link {{ request()->routeIs('admin.posts.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM8 7h8M8 11h8M8 15h5"/></svg>
                                {{ __('Posts') }}
                            </a>
                            <a href="{{ route('admin.media.index') }}" class="admin-nav-link {{ request()->routeIs('admin.media.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/></svg>
                                {{ __('Media') }}
                            </a>
                            <a href="{{ route('admin.comments.index') }}" class="admin-nav-link {{ request()->routeIs('admin.comments.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z"/></svg>
                                {{ __('Comments') }}
                            </a>
                        </div>
                    </div>

                    {{-- People & Relationships --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_people') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_people', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('People & Relationships') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('admin.people.index') }}" class="admin-nav-link {{ request()->routeIs('admin.people.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                                {{ __('People') }}
                            </a>
                            <a href="{{ route('admin.interactions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.interactions.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9a2 2 0 0 1-2-2v-1M3 8V4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-4 4V8Z"/></svg>
                                {{ __('Interactions') }}
                            </a>
                            <a href="{{ route('admin.nodes.index') }}" class="admin-nav-link {{ request()->routeIs('admin.nodes.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m7 11 10-5M7 13l10 5"/></svg>
                                {{ __('Nodes') }}
                            </a>
                            <a href="{{ route('admin.node-connections.index') }}" class="admin-nav-link {{ request()->routeIs('admin.node-connections.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                                {{ __('Node Connections') }}
                            </a>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_content') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_content', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Content') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('admin.timeline.index') }}" class="admin-nav-link {{ request()->routeIs('admin.timeline.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Timeline') }}
                            </a>
                            <a href="{{ route('admin.adages.index') }}" class="admin-nav-link {{ request()->routeIs('admin.adages.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1Zm14 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1Z"/></svg>
                                {{ __('Adages') }}
                            </a>
                        </div>
                    </div>

                    {{-- Personel --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_personel') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_personel', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Personel') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('admin.todo-items.index') }}" class="admin-nav-link {{ request()->routeIs('admin.todo-items.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Yapılacaklar') }}
                            </a>
                            <a href="{{ route('admin.purchase-items.index') }}" class="admin-nav-link {{ request()->routeIs('admin.purchase-items.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2 9m5-9v9m4-9v9m5-9-2 9"/></svg>
                                {{ __('Satın Alınacaklar') }}
                            </a>
                            <a href="{{ route('admin.bucketlist') }}" class="admin-nav-link {{ request()->routeIs('admin.bucketlist') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                                {{ __('Bucket List') }}
                            </a>
                            <a href="{{ route('admin.foods.index') }}" class="admin-nav-link {{ request()->routeIs('admin.foods.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v6.75A2.25 2.25 0 0 1 6 12H4.5m0-9v18m0 0h1.5m-1.5 0V12m15-9v18m0-13.5a3 3 0 1 0-6 0v4.5a3 3 0 1 0 6 0v-4.5Z"/></svg>
                                {{ __('Besinler') }}
                            </a>
                            <a href="{{ route('admin.consumptions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.consumptions.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Tüketimler') }}
                            </a>
                        </div>
                    </div>

                    {{-- Finance --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_finance') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_finance', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Finance') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('admin.expenses.index') }}" class="admin-nav-link {{ request()->routeIs('admin.expenses.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2Zm0 6v2m0 0a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m0 0v2"/></svg>
                                {{ __('Expenses') }}
                            </a>
                            <a href="{{ route('admin.incomes.index') }}" class="admin-nav-link {{ request()->routeIs('admin.incomes.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 7v1m0-9V4m0 16v-1"/></svg>
                                {{ __('Incomes') }}
                            </a>
                            <a href="{{ route('admin.stakeholders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.stakeholders.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                {{ __('Stakeholders') }}
                            </a>
                            <a href="{{ route('admin.reports') }}" class="admin-nav-link {{ request()->routeIs('admin.reports') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/></svg>
                                {{ __('Reports') }}
                            </a>
                        </div>
                    </div>

                    {{-- Systems --}}
                    <div
                        class="mb-1"
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_systems') ?? 'true'); } catch(e) { return true; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_systems', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Systems') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 mb-3 space-y-0.5">
                            <a href="{{ route('admin.subscribers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.subscribers.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.92 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                                {{ __('Subscribers') }}
                            </a>
                            <a href="{{ route('admin.import.index') }}" class="admin-nav-link {{ request()->routeIs('admin.import.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5 5 5 5-5m-5 5V3"/></svg>
                                {{ __('Import') }}
                            </a>
                            <a href="{{ route('admin.activity-log.index') }}" class="admin-nav-link {{ request()->routeIs('admin.activity-log.*') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5"/></svg>
                                {{ __('Activity Log') }}
                            </a>
                            <a href="{{ route('admin.about') }}" class="admin-nav-link {{ request()->routeIs('admin.about') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                {{ __('About Page') }}
                            </a>
                            <a href="{{ route('admin.settings') }}" class="admin-nav-link {{ request()->routeIs('admin.settings') ? 'admin-nav-link-active' : '' }}">
                                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"/><circle cx="12" cy="12" r="3"/></svg>
                                {{ __('Settings') }}
                            </a>
                        </div>
                    </div>

                    {{-- Dictionaries --}}
                    <div
                        x-data="{
                            open: (() => { try { return JSON.parse(localStorage.getItem('nav_dict') ?? 'false'); } catch(e) { return false; } })(),
                            toggle() { this.open = !this.open; try { localStorage.setItem('nav_dict', JSON.stringify(this.open)); } catch(e) {} }
                        }"
                    >
                        <button
                            type="button"
                            @click="toggle()"
                            class="flex w-full items-center justify-between px-3 py-1 text-left"
                        >
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ __('Dictionaries') }}</span>
                            <svg class="h-3 w-3 text-gray-400 transition-transform duration-150 dark:text-gray-500" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" class="mt-0.5 space-y-0.5">
                            @foreach ($dictionaryNavigation as $dictionaryItem)
                                <a
                                    href="{{ route('admin.dictionaries.index', $dictionaryItem['table']) }}"
                                    class="admin-nav-link {{ request()->routeIs('admin.dictionaries.*') && request()->route('table') === $dictionaryItem['table'] ? 'admin-nav-link-active' : '' }}"
                                >
                                    <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7"/></svg>
                                    {{ $dictionaryItem['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </aside>

            {{-- ====================================================
                 MAIN AREA
            ==================================================== --}}
            <div class="relative flex flex-col">

                {{-- Mobile overlay --}}
                <div
                    class="fixed inset-0 z-40 bg-gray-950/40 backdrop-blur-sm lg:hidden"
                    x-show="sidebarOpen"
                    x-transition.opacity
                    x-on:click="sidebarOpen = false"
                    x-cloak
                ></div>

                {{-- Header --}}
                <header class="sticky top-0 z-30 flex h-14 shrink-0 items-center justify-between border-b border-[var(--color-admin-border)] bg-white/95 px-4 backdrop-blur dark:border-[var(--color-admin-border-dark)] dark:bg-[#111113]/95">
                    <h1 class="text-[15px] font-semibold tracking-tight">{{ __($heading ?? 'Admin') }}</h1>

                    <div class="flex items-center gap-2">
                        {{-- Mobile hamburger --}}
                        <button
                            type="button"
                            @click="sidebarOpen = !sidebarOpen"
                            class="admin-btn admin-btn-ghost h-9 w-9 rounded-xl p-0 lg:hidden"
                            aria-label="{{ __('Toggle navigation') }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Theme toggle --}}
                        <button
                            type="button"
                            data-theme-toggle
                            class="admin-btn admin-btn-ghost h-9 w-9 rounded-xl p-0"
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
                                <button type="submit" class="admin-btn admin-btn-ghost h-9 px-3 text-xs">
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </header>

                {{-- Main content --}}
                <main id="main-content" class="flex-1 p-4 pb-20 sm:p-5 lg:p-6 lg:pb-6">
                    <x-flash />
                    @yield('content')
                </main>
            </div>
        </div>

        {{-- ====================================================
             MOBILE BOTTOM NAVIGATION
        ==================================================== --}}
        <nav
            class="admin-safe-bottom fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-[var(--color-admin-border)] bg-white/95 backdrop-blur-xl dark:border-[var(--color-admin-border-dark)] dark:bg-[#111113]/95 lg:hidden"
            aria-label="{{ __('Quick navigation') }}"
        >
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-2 {{ request()->routeIs('dashboard') ? 'text-[var(--color-brand)]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }} text-[10px] font-medium transition-colors">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                <span>{{ __('Home') }}</span>
            </a>
            <a href="{{ route('admin.posts.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-2 {{ request()->routeIs('admin.posts.*') ? 'text-[var(--color-brand)]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }} text-[10px] font-medium transition-colors">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM8 7h8M8 11h8M8 15h5"/></svg>
                <span>{{ __('Posts') }}</span>
            </a>
            <a href="{{ route('admin.people.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-2 {{ request()->routeIs('admin.people.*') ? 'text-[var(--color-brand)]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }} text-[10px] font-medium transition-colors">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                <span>{{ __('People') }}</span>
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-2 {{ request()->routeIs('admin.expenses.*') ? 'text-[var(--color-brand)]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }} text-[10px] font-medium transition-colors">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 7v1m0-9V4m0 16v-1"/></svg>
                <span>{{ __('Finance') }}</span>
            </a>
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="flex flex-col items-center gap-0.5 px-3 py-2 text-[10px] font-medium text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                aria-label="{{ __('Open menu') }}"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>{{ __('More') }}</span>
            </button>
        </nav>
        <x-app-dialog />
        <x-footer-credit />
    </body>
</html>
