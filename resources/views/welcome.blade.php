<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <x-brand-meta />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center gap-8 px-6 py-12 text-center">
            <img
                src="{{ asset('M_Logo.png') }}"
                alt="{{ config('app.name') }}"
                class="h-20 w-20 rounded-2xl object-contain shadow-sm"
            >

            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-zinc-500">{{ __('Personal Website') }}</p>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">{{ config('app.name') }}</h1>
                <p class="mx-auto max-w-xl text-sm leading-7 text-zinc-600 sm:text-base">
                    {{ __('Blog-first personal site with timeline, biolink, and admin publishing workflow.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="rounded-full bg-zinc-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-zinc-800">
                    {{ __('Go to Homepage') }}
                </a>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-zinc-300 px-6 py-2.5 text-sm font-medium text-zinc-800 hover:bg-zinc-100">
                            {{ __('Admin Panel') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-zinc-300 px-6 py-2.5 text-sm font-medium text-zinc-800 hover:bg-zinc-100">
                            {{ __('Login') }}
                        </a>
                    @endauth
                @endif
            </div>
        </main>
        <x-footer-credit />
    </body>
</html>
