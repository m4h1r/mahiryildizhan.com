@extends('public.layout')

@section('content')
    @if ($latestPost ?? null)
    <section class="public-fade-up overflow-hidden rounded-[2.2rem] border border-gray-200 bg-white shadow-[0_24px_60px_-42px_rgba(15,23,42,0.38)] dark:border-gray-800 dark:bg-gray-900">
        @if ($latestPost->cover_url)
            <a href="{{ route('blog.show', $latestPost->slug) }}" class="block overflow-hidden">
                <img
                    src="{{ $latestPost->cover_url }}"
                    alt="{{ $latestPost->title }}"
                    class="aspect-[16/7] w-full object-cover transition duration-500 hover:scale-[1.02]"
                >
            </a>
        @endif
        <div class="relative overflow-hidden p-7 md:p-10">
            @unless ($latestPost->cover_url)
                <div class="pointer-events-none absolute -top-28 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-[#0071e3]/15 blur-3xl"></div>
            @endunless
            <div class="relative">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-500">
                    {{ optional($latestPost->category)->name ?: __('Latest Story') }}
                </p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-[-0.03em] text-[#1d1d1f] dark:text-gray-100 md:text-5xl">
                    <a href="{{ route('blog.show', $latestPost->slug) }}" class="hover:underline">{{ $latestPost->title }}</a>
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-[#6e6e73] dark:text-gray-300">
                    {{ $latestPost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($latestPost->body), 200) }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-4">
                    <a href="{{ route('blog.show', $latestPost->slug) }}" class="public-pill-btn rounded-full bg-[#1d1d1f] px-6 text-sm text-white hover:bg-black dark:bg-white dark:text-black">
                        {{ __('Read Article') }} →
                    </a>
                    <span class="text-sm text-gray-500">{{ optional($latestPost->published_at)->format('d.m.Y') ?: optional($latestPost->created_at)->format('d.m.Y') }}</span>
                    @if ($latestPost->reading_time)
                        <span class="text-sm text-gray-500">{{ __(':minutes min read', ['minutes' => max(1, (int) $latestPost->reading_time)]) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @else
    <section class="public-fade-up relative overflow-hidden rounded-[2.2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] p-7 shadow-[0_24px_60px_-42px_rgba(15,23,42,0.38)] dark:border-gray-800 dark:from-gray-900 dark:to-gray-950 md:p-12">
        <div class="pointer-events-none absolute -top-28 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-[#0071e3]/15 blur-3xl"></div>
        <div class="relative text-center">
            <p class="public-fade-up public-delay-1 text-[11px] font-semibold uppercase tracking-[0.26em] text-gray-500">{{ __('Editorial Journal') }}</p>
            <h1 class="public-fade-up public-delay-2 mx-auto mt-4 max-w-4xl text-4xl font-semibold leading-tight tracking-[-0.03em] text-[#1d1d1f] dark:text-gray-100 md:text-6xl">
                {{ __('Thoughtful writing, crafted with a modern and quiet interface.') }}
            </h1>
            <div class="public-fade-up public-delay-3 mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('blog.index') }}" class="public-pill-btn rounded-full bg-[#1d1d1f] px-6 text-sm text-white hover:bg-black dark:bg-white dark:text-black">{{ __('Read the Blog') }}</a>
            </div>
        </div>
    </section>
    @endif

    <section class="mt-16">
        <div class="mb-6 flex items-end justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-500">{{ __('Latest Stories') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100 md:text-3xl">{{ __('Fresh from the journal') }}</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[#0071e3] hover:underline">{{ __('View all') }}</a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @forelse (($featuredPosts ?? collect()) as $post)
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
                    <div class="p-6">
                        <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                            {{ optional($post->category)->name ?: __('General') }}
                        </p>
                        <h3 class="mt-3 line-clamp-2 text-xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a>
                        </h3>
                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-[#6e6e73] dark:text-gray-300">
                            {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}
                        </p>
                        <p class="mt-4 text-xs text-gray-500">{{ optional($post->published_at)->format('d.m.Y') ?: optional($post->created_at)->format('d.m.Y') }}</p>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 md:col-span-3">
                    {{ __('Published stories will appear here as soon as they are available.') }}
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-14">
        <div class="public-card p-6 md:p-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500">{{ __('Newsletter') }}</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-[#1d1d1f] dark:text-gray-100">{{ __('Get new posts by email') }}</h2>
                </div>

                <form method="POST" action="{{ route('public.subscribers.store') }}" class="flex w-full flex-col gap-3 md:w-auto md:flex-row">
                    @csrf
                    <label class="sr-only" for="newsletter_email">{{ __('Email') }}</label>
                    <input id="newsletter_email" type="email" name="email" required class="form-input-admin min-w-[18rem] rounded-full" placeholder="ornek@eposta.com" value="{{ old('email') }}">
                    <button type="submit" class="public-pill-btn bg-[#1d1d1f] text-white hover:bg-black dark:bg-white dark:text-black">{{ __('Subscribe') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
