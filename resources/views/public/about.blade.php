@extends('public.layout', [
    'title' => __('About').' | '.config('app.name'),
    'description' => __('About the editorial direction and background of this website.'),
])

@section('content')
    <article class="mx-auto max-w-3xl space-y-6">
        <header class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ __('About') }}</p>
            <h1 class="public-editorial-heading text-4xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100 md:text-5xl">
                {{ config('app.name') }}
            </h1>
            <p class="text-base leading-8 text-gray-700 dark:text-gray-300">
                {{ __('This site combines long-form writing, timeline storytelling, and a lightweight admin workflow for publishing and personal knowledge management.') }}
            </p>
        </header>

        <section class="rounded-2xl border border-gray-200 bg-white/85 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
            <h2 class="text-lg font-semibold">{{ __('Focus') }}</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-gray-700 dark:text-gray-300">
                <li>{{ __('Blog-first public experience with clear typography.') }}</li>
                <li>{{ __('Structured content systems for posts, timeline, and biolink.') }}</li>
                <li>{{ __('Practical admin tools for content and finance tracking.') }}</li>
            </ul>
        </section>
    </article>
@endsection
