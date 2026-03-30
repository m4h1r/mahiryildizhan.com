<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="ui()" x-init="init()" :class="{ dark: dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>
        <x-brand-meta />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            Skip to content
        </a>

        <div id="main-content" class="min-h-screen flex flex-col items-center pt-6 sm:justify-center sm:pt-0 bg-gray-100 dark:bg-gray-950">
            <div>
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo class="h-12 w-12 rounded-xl object-contain" />
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:mt-6 sm:max-w-md sm:rounded-lg dark:bg-gray-900">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
