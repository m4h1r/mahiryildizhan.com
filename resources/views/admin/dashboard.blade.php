@extends('admin.layout', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
    @php
        $dailyTimes = $weather['daily']['time'] ?? [];
        $dailyMin = $weather['daily']['temperature_2m_min'] ?? [];
        $dailyMax = $weather['daily']['temperature_2m_max'] ?? [];

        $fxUsdTry = data_get($rates, 'fx.rates.USD') ? 1 / (float) data_get($rates, 'fx.rates.USD') : null;
        $fxEurTry = data_get($rates, 'fx.rates.EUR') ? 1 / (float) data_get($rates, 'fx.rates.EUR') : null;
        $fxGbpTry = data_get($rates, 'fx.rates.GBP') ? 1 / (float) data_get($rates, 'fx.rates.GBP') : null;

        $btcUsd = (float) data_get($rates, 'crypto.bitcoin.usd', 0);
        $ethUsd = (float) data_get($rates, 'crypto.ethereum.usd', 0);
        $isNetNegative = $monthlyNet < 0;
        $annualNet = $annualIncome - $annualExpense;
        $isAnnualNetNegative = $annualNet < 0;

        $weatherCodeToIcon = [
            0 => '☀️', 1 => '🌤️', 2 => '⛅', 3 => '☁️',
            45 => '🌫️', 48 => '🌫️',
            51 => '🌦️', 53 => '🌦️', 55 => '🌦️', 56 => '🌧️', 57 => '🌧️',
            61 => '🌧️', 63 => '🌧️', 65 => '🌧️',
            66 => '🌨️', 67 => '🌨️',
            71 => '❄️', 73 => '❄️', 75 => '❄️', 77 => '❄️',
            80 => '🌧️', 81 => '🌧️', 82 => '⛈️',
            85 => '🌨️', 86 => '🌨️',
            95 => '⛈️', 96 => '⛈️', 99 => '⛈️',
        ];
    @endphp

    <div class="space-y-5" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 500)">
        <section x-cloak x-show="!loading" class="card-admin border border-blue-200/80 bg-gradient-to-br from-blue-50 to-indigo-50 dark:border-blue-800 dark:from-blue-950/40 dark:to-indigo-950/40">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $weatherCityName }} {{ __('Weather') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('5-day outlook') }}</p>
            </div>

            @if (!empty($dailyTimes))
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                    @foreach (array_slice($dailyTimes, 0, 5) as $index => $date)
                        @php
                            $code = (int) ($weather['daily']['weathercode'][$index] ?? -1);
                            $icon = $weatherCodeToIcon[$code] ?? '🌤️';
                        @endphp
                        <article class="rounded-xl border border-blue-200/70 bg-white/80 p-3 text-center shadow-sm transition hover:shadow-md dark:border-blue-800/60 dark:bg-gray-900/50">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Carbon::parse($date)->format('D, d M') }}</p>
                            <p class="mt-2 text-3xl">{{ $icon }}</p>
                            <p class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $dailyMax[$index] ?? '-' }}° / {{ $dailyMin[$index] ?? '-' }}°
                            </p>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Weather data unavailable right now.') }}</p>
            @endif
        </section>

        <section x-cloak x-show="!loading" class="card-admin border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-teal-50 dark:border-emerald-800 dark:from-emerald-950/40 dark:to-teal-950/40">
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ now()->year }} {{ __('Financial Status') }}</p>
            <div class="mt-3 flex flex-col items-center gap-2 text-center text-base font-extrabold leading-snug sm:flex-row sm:flex-wrap sm:justify-center sm:text-lg md:text-2xl">
                <span class="rounded-lg bg-white/70 px-2 py-1 text-green-700 dark:bg-gray-900/40 dark:text-green-300">{{ number_format($annualIncome, 2) }} TRY</span>
                <span class="text-gray-500 dark:text-gray-400">-</span>
                <span class="rounded-lg bg-white/70 px-2 py-1 text-red-700 dark:bg-gray-900/40 dark:text-red-300">{{ number_format($annualExpense, 2) }} TRY</span>
                <span class="text-gray-500 dark:text-gray-400">=</span>
                <span class="rounded-lg bg-white/70 px-2 py-1 {{ $isAnnualNetNegative ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }} dark:bg-gray-900/40">{{ number_format($annualNet, 2) }} TRY</span>
            </div>
        </section>

        <section x-cloak x-show="!loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <article class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-green-100 p-3 dark:border-green-800 dark:from-green-950/40 dark:to-green-900/40">
                <p class="text-xs text-gray-600 dark:text-gray-300">USD/TRY</p>
                <p class="mt-1 text-lg font-bold text-green-700 dark:text-green-300">{{ $fxUsdTry ? number_format($fxUsdTry, 4) : '-' }}</p>
            </article>
            <article class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-3 dark:border-indigo-800 dark:from-indigo-950/40 dark:to-indigo-900/40">
                <p class="text-xs text-gray-600 dark:text-gray-300">EUR/TRY</p>
                <p class="mt-1 text-lg font-bold text-indigo-700 dark:text-indigo-300">{{ $fxEurTry ? number_format($fxEurTry, 4) : '-' }}</p>
            </article>
            <article class="hidden rounded-xl border border-violet-200 bg-gradient-to-br from-violet-50 to-violet-100 p-3 md:block dark:border-violet-800 dark:from-violet-950/40 dark:to-violet-900/40">
                <p class="text-xs text-gray-600 dark:text-gray-300">GBP/TRY</p>
                <p class="mt-1 text-lg font-bold text-violet-700 dark:text-violet-300">{{ $fxGbpTry ? number_format($fxGbpTry, 4) : '-' }}</p>
            </article>
            <article class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100 p-3 dark:border-amber-800 dark:from-amber-950/40 dark:to-amber-900/40">
                <p class="text-xs text-gray-600 dark:text-gray-300">BTC/USD</p>
                <p class="mt-1 text-lg font-bold text-amber-700 dark:text-amber-300">${{ number_format($btcUsd, 2) }}</p>
            </article>
            <article class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100 p-3 dark:border-blue-800 dark:from-blue-950/40 dark:to-blue-900/40">
                <p class="text-xs text-gray-600 dark:text-gray-300">ETH/USD</p>
                <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-300">${{ number_format($ethUsd, 2) }}</p>
            </article>
        </section>

        <section x-cloak x-show="!loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="card-admin border-l-4 border-blue-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Published Posts') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-blue-700 dark:text-blue-300">{{ number_format($publishedPosts) }}</p>
            </article>
            <article class="card-admin border-l-4 border-orange-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pending Comments') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-orange-700 dark:text-orange-300">{{ number_format($pendingComments) }}</p>
            </article>
            <article class="card-admin border-l-4 border-emerald-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Monthly Net') }}</p>
                <p class="mt-2 text-3xl font-extrabold {{ $isNetNegative ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($monthlyNet, 2) }} TRY</p>
            </article>
        </section>

        <section x-cloak x-show="!loading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[1.2fr_1.2fr_1fr]">
            <article class="card-admin">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Birthdays This Week') }}</h3>
                    <a href="{{ route('admin.people.index') }}" class="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300">{{ __('View all') }}</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($upcomingBirthdays as $person)
                        @php($daysFromToday = (int) $person->days_from_today)
                        <a href="{{ route('admin.people.show', $person->id) }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-900/60">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ trim(($person->name ?? '') . ' ' . ($person->surname ?? '')) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($person->closest_birthday)->translatedFormat('d M') }}</p>
                            </div>
                            <span class="text-xs font-semibold {{ $daysFromToday === 0 ? 'text-rose-600 dark:text-rose-300' : 'text-gray-500 dark:text-gray-300' }}">
                                @if ($daysFromToday === 0)
                                    {{ __('Today') }}
                                @elseif ($daysFromToday < 0)
                                    {{ __(':day days ago', ['day' => abs($daysFromToday)]) }}
                                @else
                                    {{ __('In :day days', ['day' => $daysFromToday]) }}
                                @endif
                            </span>
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No birthdays found in this range.') }}</p>
                    @endforelse
                </div>
            </article>

            <article class="card-admin">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Latest Posts') }}</h3>
                    <a href="{{ route('admin.posts.index') }}" class="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300">{{ __('Manage') }}</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($recentPosts as $post)
                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="block rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-900/60">
                            <p class="line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $post->title }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Status') }}: {{ ucfirst((string) $post->status) }}
                                <span class="mx-1">•</span>
                                {{ optional($post->updated_at)->diffForHumans() }}
                            </p>
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No posts yet.') }}</p>
                    @endforelse
                </div>
            </article>

            <article class="space-y-4">
                <div class="card-admin border border-purple-200/70 bg-gradient-to-br from-purple-50 to-fuchsia-50 dark:border-purple-800/70 dark:from-purple-950/30 dark:to-fuchsia-950/30">
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('This Year') }}</p>
                    <div class="mt-3 space-y-2">
                        <p class="flex items-center justify-between text-sm"><span>{{ __('Income') }}</span><span class="font-bold text-green-700 dark:text-green-300">{{ number_format($annualIncome, 2) }} TRY</span></p>
                        <p class="flex items-center justify-between text-sm"><span>{{ __('Expense') }}</span><span class="font-bold text-red-700 dark:text-red-300">{{ number_format($annualExpense, 2) }} TRY</span></p>
                        <p class="flex items-center justify-between border-t border-gray-200 pt-2 text-sm dark:border-gray-700"><span>{{ __('Net') }}</span><span class="font-bold {{ ($annualIncome - $annualExpense) < 0 ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($annualIncome - $annualExpense, 2) }} TRY</span></p>
                    </div>
                </div>

                <div class="card-admin border border-cyan-200/70 bg-gradient-to-br from-cyan-50 to-sky-50 dark:border-cyan-800/70 dark:from-cyan-950/30 dark:to-sky-950/30">
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Quick Stats') }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-center">
                        <div class="rounded-lg bg-white/70 px-3 py-2 dark:bg-gray-900/60">
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ __('People') }}</p>
                            <p class="text-lg font-bold text-cyan-700 dark:text-cyan-300">{{ number_format($peopleCount) }}</p>
                        </div>
                        <div class="rounded-lg bg-white/70 px-3 py-2 dark:bg-gray-900/60">
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ __('Approved') }}</p>
                            <p class="text-lg font-bold text-cyan-700 dark:text-cyan-300">{{ number_format($approvedComments) }}</p>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section x-cloak x-show="!loading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[1.3fr_1fr]">
            <article class="card-admin">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Recent Comments') }}</h3>
                    <a href="{{ route('admin.comments.index') }}" class="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300">{{ __('Moderate') }}</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($recentComments as $comment)
                        <div class="rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $comment->guest_name ?: __('Guest') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($comment->post)->title ?: __('Unknown post') }}</p>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $comment->is_approved ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' }}">
                                    {{ $comment->is_approved ? __('Approved') : __('Pending') }}
                                </span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-300">{{ $comment->body }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ optional($comment->created_at)->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No comments yet.') }}</p>
                    @endforelse
                </div>
            </article>

            <article class="card-admin border border-gray-300/70 bg-gradient-to-br from-gray-50 to-white dark:border-gray-700 dark:from-gray-900/40 dark:to-gray-950/20">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Quick Actions') }}</h3>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('admin.posts.create') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Create New Post') }}</a>
                    <a href="{{ route('admin.people.create') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Add New Person') }}</a>
                    <a href="{{ route('admin.reports') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Open Financial Reports') }}</a>
                    <a href="{{ route('admin.import.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Run CSV Import') }}</a>
                </div>
            </article>
        </section>

        <template x-if="loading">
            <section class="grid gap-4 md:grid-cols-3">
                <div class="h-24 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700"></div>
                <div class="h-24 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700"></div>
                <div class="h-24 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700"></div>
            </section>
        </template>
    </div>
@endsection
