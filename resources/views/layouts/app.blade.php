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
    <body class="font-sans antialiased bg-gray-100 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
            Skip to content
        </a>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-950">
            @include('layouts.navigation')

            <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <x-flash />
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow dark:bg-gray-900">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content">
                {{ $slot }}
            </main>
        </div>
        <x-footer-credit />
    </body>
</html>
