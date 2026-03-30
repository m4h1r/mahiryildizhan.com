@extends('public.layout')

@section('content')
    <section class="space-y-10">
        <div class="public-fade-up rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] p-6 dark:border-gray-800 dark:from-gray-900 dark:to-gray-950 md:p-10">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Blog</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-[-0.02em] text-[#1d1d1f] dark:text-gray-100 md:text-5xl">{{ __('Latest Articles') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-[#6e6e73] dark:text-gray-300 md:text-base">
                    {{ __('Long-form notes, ideas, and updates. Designed for calm reading and simple navigation.') }}
                </p>
            </div>

            <form method="GET" class="public-fade-up public-delay-1 mt-6 flex w-full gap-2 md:w-[32rem]">
                <input
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="{{ __('Search posts...') }}"
                    class="form-input-admin w-full rounded-full"
                >
                <button type="submit" class="public-pill-btn bg-[#1d1d1f] px-5 text-white hover:bg-black dark:bg-white dark:text-black">{{ __('Search') }}</button>
            </form>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            @forelse ($posts as $post)
                <article class="public-card public-fade-up overflow-hidden">
                    @if ($post->cover_url)
                        <a href="{{ route('blog.show', $post->slug) }}" class="block overflow-hidden">
                            <img
                                src="{{ $post->cover_url }}"
                                alt="{{ $post->title }}"
                                class="aspect-video w-full object-cover transition duration-300 hover:scale-105"
                            >
                        </a>
                    @endif
                    <div class="p-7">
                        <p class="text-xs uppercase tracking-[0.16em] text-gray-500">
                            {{ optional($post->category)->name ?: __('General') }}
                            @if ($post->language)
                                · {{ strtoupper($post->language->code) }}
                            @endif
                        </p>

                        <h2 class="mt-3 text-2xl font-semibold leading-tight tracking-tight text-[#1d1d1f] dark:text-gray-100">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a>
                        </h2>

                        <p class="mt-3 text-sm leading-relaxed text-[#6e6e73] dark:text-gray-300">
                            {{ $post->excerpt ?: \Illuminate\Support\Str::of(strip_tags($post->body))->limit(180, '...') }}
                        </p>

                        <div class="mt-5 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ optional($post->published_at)->format('d.m.Y') ?: optional($post->created_at)->format('d.m.Y') }}</span>
                            <span>{{ __(':minutes min read', ['minutes' => max(1, (int) $post->reading_time)]) }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 p-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400 md:col-span-2">
                    {{ __('No posts found.') }}
                </div>
            @endforelse
        </div>

        <div class="public-fade-up public-delay-2 pt-2">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
