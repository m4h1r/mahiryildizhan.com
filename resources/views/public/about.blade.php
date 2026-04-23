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
        </header>

        @if(app()->getLocale() === 'tr')
            <x-about-tr />
        @else
            <x-about-en />
        @endif
    </article>
@endsection
