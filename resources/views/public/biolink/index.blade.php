@extends('public.layout')

@section('content')
    <section class="space-y-8">
        <header class="public-fade-up rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] p-8 dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">{{ __('Biolink') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100 md:text-5xl">{{ __('Useful links and downloads') }}</h1>
        </header>

        <div class="grid gap-4">
            @forelse ($links as $link)
                <a href="{{ route('links.show', $link->slug) }}" class="public-card flex items-center justify-between p-5">
                    <div>
                        <p class="text-sm font-semibold text-[#1d1d1f] dark:text-gray-100">{{ $link->original_name }}</p>
                        <p class="text-xs text-gray-500">/{{ $link->slug }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ number_format($link->download_count) }} {{ __('downloads') }}</span>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 p-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No links published yet.') }}</div>
            @endforelse
        </div>
    </section>
@endsection