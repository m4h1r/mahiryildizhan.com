@extends('public.layout')

@section('content')
    <section class="grid gap-8 md:grid-cols-[1.2fr_0.8fr] md:items-center">
        <div>
            <p class="text-xs uppercase tracking-[0.22em] text-gray-500">CRM + Blog Rebuild</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight md:text-6xl">
                Minimal structure for a fast admin and readable public experience.
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-relaxed text-gray-600 dark:text-gray-300">
                Phase 0 is complete: skeleton routes, security middleware, dark mode, local fonts, and accessibility foundations are now in place.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="rounded-lg bg-[var(--color-brand)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--color-brand-dark)]">
                    Open Admin
                </a>
                <a href="{{ route('locale.switch', 'tr') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700">TR</a>
                <a href="{{ route('locale.switch', 'en') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700">EN</a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500">Phase 0 Checklist</h2>
            <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                <li>Route split: web/admin/public/api</li>
                <li>EnsureAdmin + SetLocale + SecurityHeaders</li>
                <li>Dark mode and local font stack</li>
                <li>x-flash component + skeleton pages</li>
                <li>Custom 404/500/503 pages</li>
            </ul>
        </div>
    </section>
@endsection
