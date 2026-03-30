@extends('public.layout')

@section('content')
    <section class="space-y-8">
        <header class="public-fade-up rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] p-8 dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">{{ __('Search') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100 md:text-5xl">{{ __('Find posts, adages, and people') }}</h1>
            <form method="GET" action="{{ route('search') }}" class="mt-6 flex gap-2">
                <input class="form-input-admin rounded-full" name="q" value="{{ $query }}" placeholder="{{ __('Search...') }}">
                <button type="submit" class="public-pill-btn bg-[#1d1d1f] text-white hover:bg-black dark:bg-white dark:text-black">{{ __('Search') }}</button>
            </form>
        </header>

        @if ($query !== '')
            <div class="grid gap-6 lg:grid-cols-3">
                <article class="public-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ __('Posts') }}</h2>
                    <div class="mt-3 space-y-3">
                        @forelse ($posts as $post)
                            <a class="block text-sm font-medium hover:underline" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No matching posts.') }}</p>
                        @endforelse
                    </div>
                </article>

                <article class="public-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ __('Adages') }}</h2>
                    <div class="mt-3 space-y-3">
                        @forelse ($adages as $adage)
                            <div>
                                <p class="text-sm font-medium">{{ $adage->owner }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($adage->adage, 120) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No matching adages.') }}</p>
                        @endforelse
                    </div>
                </article>

                <article class="public-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500">{{ __('People') }}</h2>
                    <div class="mt-3 space-y-3">
                        @forelse ($people as $person)
                            <p class="text-sm">{{ trim($person->name.' '.$person->surname) }}</p>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No matching people.') }}</p>
                        @endforelse
                    </div>
                </article>
            </div>
        @endif
    </section>
@endsection