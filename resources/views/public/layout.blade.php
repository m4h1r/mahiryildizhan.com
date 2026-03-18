<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="ui()" x-init="init()" :class="{ dark: dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ $robots ?? 'index,follow' }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-surface)] text-gray-900 antialiased dark:bg-[var(--color-surface-dark)] dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            Skip to content
        </a>

        <header class="border-b border-gray-200 bg-white/80 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700">Admin Login</a>
                    <button
                        type="button"
                        @click="toggleTheme()"
                        class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium dark:border-gray-700"
                        aria-label="Toggle theme"
                    >
                        <span x-show="!dark">Dark</span>
                        <span x-show="dark">Light</span>
                    </button>
                </div>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-6xl px-4 py-12">
            <x-flash />
            @yield('content')
        </main>
    </body>
</html>
