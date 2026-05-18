@extends('public.layout')

@section('content')
    <div id="reading-progress" class="fixed left-0 right-0 top-[65px] z-30 h-[2px] origin-left scale-x-0 bg-[#0071e3] transition-transform duration-150"></div>

    <article class="mx-auto max-w-3xl">
        <div class="public-fade-up mb-10 overflow-hidden rounded-[2rem] border border-gray-200 bg-gradient-to-b from-white to-[#f5f5f7] dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
            @if ($post->cover_url)
                <div class="aspect-[2/1] overflow-hidden">
                    <img
                        src="{{ $post->cover_url }}"
                        alt="{{ $post->title }}"
                        class="h-full w-full object-cover"
                    >
                </div>
            @endif
            <div class="p-6 md:p-10">
                <p class="text-xs uppercase tracking-[0.16em] text-gray-500">
                    {{ optional($post->category)->name ?: __('General') }}
                    @if ($post->language)
                        · {{ strtoupper($post->language->code) }}
                    @endif
                </p>

                <h1 class="mt-3 text-4xl font-semibold leading-tight tracking-[-0.02em] text-[#1d1d1f] dark:text-gray-100 md:text-5xl">{{ $post->title }}</h1>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                    <span>{{ optional($post->published_at)->format('d.m.Y') ?: optional($post->created_at)->format('d.m.Y') }}</span>
                    <span>{{ __(':minutes min read', ['minutes' => max(1, (int) $post->reading_time)]) }}</span>
                    <span>{{ number_format((int) $post->view_count) }} {{ __('views') }}</span>
                    <span>{{ number_format((int) $post->unique_view_count) }} {{ __('unique') }}</span>
                </div>
            </div>
        </div>

        <div id="article-body" class="prose-blog break-all">
            {!! $post->body !!}
        </div>

        <div class="mt-10 border-t border-gray-200 pt-6 dark:border-gray-800">
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[#0071e3] hover:underline">← {{ __('Back to blog') }}</a>
        </div>

        <section class="mt-12 border-t border-gray-200 pt-8 dark:border-gray-800">
            <h2 class="text-2xl font-semibold">{{ __('Comments') }} ({{ $post->comments->count() }})</h2>

            @if ($errors->has('comment'))
                <p class="mt-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    {{ $errors->first('comment') }}
                </p>
            @endif

            <form method="POST" action="{{ route('public.comments.store') }}" class="public-card mt-6 space-y-4 p-6">
                @csrf
                <input type="hidden" name="post_id" value="{{ $post->id }}">
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                <div class="hidden" aria-hidden="true">
                    <label>Website
                        <input type="text" name="website" value="{{ old('website') }}" tabindex="-1" autocomplete="off">
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        <span class="mb-1 block">{{ __('Name') }}</span>
                        <input name="guest_name" class="form-input-admin" value="{{ old('guest_name', auth()->user()?->name) }}">
                    </label>

                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        <span class="mb-1 block">{{ __('Email') }}</span>
                        <input type="email" name="guest_email" class="form-input-admin" value="{{ old('guest_email', auth()->user()?->email) }}">
                    </label>
                </div>

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    <span class="mb-1 block">{{ __('Comment') }}</span>
                    <textarea name="body" class="form-input-admin min-h-28" required>{{ old('body') }}</textarea>
                </label>

                <button type="submit" class="public-pill-btn bg-[#1d1d1f] text-white hover:bg-black dark:bg-white dark:text-black">
                    {{ __('Send Comment') }}
                </button>
            </form>

            <div class="mt-8 space-y-4">
                @forelse ($post->comments as $comment)
                    <article class="public-card p-5">
                        <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $comment->guest_name ?: optional($comment->user)->name ?: __('Anonymous') }}</span>
                            <span>{{ optional($comment->created_at)->format('d.m.Y H:i') }}</span>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ $comment->body }}</p>
                    </article>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No comments yet.') }}</p>
                @endforelse
            </div>
        </section>
    </article>

    @if (config('services.recaptcha.site_key'))
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}" src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
            grecaptcha.ready(function () {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'comment'}).then(function (token) {
                    var field = document.getElementById('recaptcha_token');

                    if (field) {
                        field.value = token;
                    }
                });
            });
        </script>
    @endif

    <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
        (function () {
            const progress = document.getElementById('reading-progress');
            const article = document.getElementById('article-body');

            if (!progress || !article) {
                return;
            }

            const update = () => {
                const rect = article.getBoundingClientRect();
                const total = rect.height - window.innerHeight;

                if (total <= 0) {
                    progress.style.transform = 'scaleX(1)';
                    return;
                }

                const consumed = Math.min(Math.max(-rect.top, 0), total);
                const ratio = consumed / total;
                progress.style.transform = `scaleX(${ratio})`;
            };

            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        })();
    </script>
@endsection
