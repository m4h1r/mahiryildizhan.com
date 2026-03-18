<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="ui()" x-init="init()" :class="{ dark: dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Admin Panel' }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            Skip to content
        </a>

        <div class="grid min-h-screen lg:grid-cols-[260px_1fr]">
            <aside class="border-r border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <a href="{{ route('dashboard') }}" class="mb-8 block text-lg font-semibold tracking-tight">
                    CRM + Blog Admin
                </a>

                <nav class="space-y-2 text-sm">
                    <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</a>
                    <a href="{{ route('admin.reports') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Reports</a>
                    <a href="{{ route('admin.settings') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Settings</a>
                </nav>
            </aside>

            <div>
                <header class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <h1 class="text-base font-semibold">{{ $heading ?? 'Admin' }}</h1>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="toggleTheme()"
                            class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700"
                            aria-label="Toggle theme"
                        >
                            <span x-show="!dark">Dark</span>
                            <span x-show="dark">Light</span>
                        </button>

                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">
                                    Logout
                                </button>
                            </form>
                        @endauth
                    </div>
                </header>

                <main id="main-content" class="p-6">
                    <x-flash />
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
