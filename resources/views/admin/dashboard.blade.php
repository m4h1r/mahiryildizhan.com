@extends('admin.layout', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
    {{-- Günün Saati — tek, neon parlamalı 24 saatlik kadran --}}
    <section class="card-admin mb-6 flex flex-col items-center gap-4 p-8">
        <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('Günün Saati') }}</h2>

        <div class="relative flex h-52 w-52 items-center justify-center">
            {{-- Alt gölge (yerde duruyormuş hissi) --}}
            <div class="absolute bottom-1 left-1/2 h-5 w-28 -translate-x-1/2 rounded-full bg-black/25 blur-lg dark:bg-black/55" aria-hidden="true"></div>

            {{-- Neon kadran halkası --}}
            <div
                class="clock-neon-ring relative h-48 w-48 rounded-full"
                style="
                    background: conic-gradient({{ $clockRing['gradient'] }});
                    box-shadow:
                        0 0 14px 1px {{ $clockRing['currentColor'] }}99,
                        0 0 34px 8px {{ $clockRing['currentColor'] }}4D;
                "
            >
                {{-- Merkez disk --}}
                <div
                    class="absolute inset-[15%] flex flex-col items-center justify-center rounded-full text-center"
                    style="
                        background:
                            radial-gradient(circle at 32% 26%, rgb(255 255 255 / 0.35), transparent 55%),
                            {{ $clockRing['currentColor'] }};
                        box-shadow:
                            inset 0 -8px 14px rgb(0 0 0 / 0.18),
                            inset 0 2px 3px rgb(255 255 255 / 0.4);
                        color: {{ $clockRing['currentTextColor'] }};
                    "
                >
                    <span class="text-xs font-medium tracking-wide opacity-80">{{ $clockRing['currentTime'] }}</span>
                    <span class="max-w-[75%] text-sm font-bold leading-tight [text-shadow:0_1px_1px_rgb(0_0_0/0.15)]">{{ $clockRing['currentLabel'] }}</span>
                </div>

                {{-- Saat işaretleri --}}
                <span class="absolute left-1/2 top-2 -translate-x-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">00</span>
                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">06</span>
                <span class="absolute bottom-2 left-1/2 -translate-x-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">12</span>
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">18</span>
            </div>
        </div>

        <a href="{{ route('admin.settings', ['tab' => 'time_ranges']) }}" class="text-xs text-gray-400 hover:underline dark:text-gray-500">{{ __('Zaman aralıklarını düzenle →') }}</a>
    </section>

    {{-- =====================================================
         KİŞİSEL DASHBOARD — mevcuta dokunma, bu div bağımsız
    ===================================================== --}}
    <div id="personal-dashboard" class="mb-6 space-y-4">

        {{-- SATIR 1: Kasa + Pasif Gelir --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Kasa ₺ (+ USD karşılığı kur ile) --}}
            <article class="card-admin border border-emerald-200/70 bg-gradient-to-br from-emerald-50 to-teal-50 dark:border-emerald-800/60 dark:from-emerald-950/30 dark:to-teal-950/30">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kasa</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">₺{{ number_format($treasuryTry, 2) }}</p>
                @if ($treasuryUsd !== null)
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">≈ ${{ number_format($treasuryUsd, 0) }}</p>
                @endif
                @if ($treasuryUsd !== null)
                    @php
                        $tierDivisor = $currentTierIndex >= 0
                            ? $wealthThresholds[$currentTierIndex]
                            : $wealthThresholds[0];
                        $monthsOfIncome = $tierDivisor > 0 ? round($treasuryUsd / $tierDivisor, 1) : null;
                    @endphp
                    @if ($monthsOfIncome !== null)
                        <p class="mt-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ $monthsOfIncome }} aylık gelire sahipsin
                        </p>
                    @endif
                @endif
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    <a href="{{ route('admin.settings') }}" class="hover:underline">Güncelle →</a>
                </p>
            </article>

            {{-- Günlük Pasif Gelir ₺ (+ USD karşılığı) --}}
            <article class="card-admin border border-sky-200/70 bg-gradient-to-br from-sky-50 to-blue-50 dark:border-sky-800/60 dark:from-sky-950/30 dark:to-blue-950/30">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Günlük Pasif Gelir</p>
                <p class="mt-2 text-2xl font-extrabold text-sky-700 dark:text-sky-300">
                    ₺{{ number_format($dailyPassiveIncomeTry, 2) }}<span class="text-sm font-normal text-gray-500 dark:text-gray-400">/gün</span>
                </p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                    ₺{{ number_format($monthlyPassiveIncomeTry, 0) }}/ay
                    @if ($monthlyPassiveIncomeUsd > 0)
                        · ≈ ${{ number_format($monthlyPassiveIncomeUsd, 0) }}/ay
                    @endif
                </p>
            </article>
        </section>

        {{-- SATIR 2: Zenginlik Seviyesi — Segmentli Progress Bar --}}
        @php
            $romanNumerals = ['I','II','III','IV','V','VI','VII','VIII','IX','X'];
        @endphp
        <section class="card-admin border border-blue-200/80 bg-gradient-to-br from-blue-50 to-indigo-50 dark:border-blue-800/60 dark:from-blue-950/30 dark:to-indigo-950/30">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Zenginlik Seviyesi</p>
                    @if ($currentTierIndex === 9)
                        <p class="mt-1 text-xl font-extrabold text-blue-700 dark:text-blue-300">X. Kademe — Zirve 🏆</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">$50.000/ay hedefine ulaştın</p>
                    @elseif ($currentTierIndex >= 0)
                        <p class="mt-1 text-xl font-extrabold text-blue-700 dark:text-blue-300">{{ $romanNumerals[$currentTierIndex] }}. Kademe</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ${{ number_format($wealthThresholds[$currentTierIndex]) }}/ay →
                            <span class="font-medium text-blue-700 dark:text-blue-400">{{ $romanNumerals[$currentTierIndex + 1] }}. Kademe</span>
                            (${{ number_format($wealthThresholds[$currentTierIndex + 1]) }}/ay)
                        </p>
                    @else
                        <p class="mt-1 text-base font-bold text-gray-500 dark:text-gray-400">I. Kademe öncesi</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Hedef: I. Kademe — $250/ay pasif gelir</p>
                    @endif
                </div>
                <span class="shrink-0 rounded-lg bg-blue-100 px-3 py-1.5 text-base font-extrabold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                    %{{ number_format($wealthProgress, 2) }}
                </span>
            </div>

            {{-- Segmentli 10-kademe progress bar --}}
            <div class="space-y-2">
                <div class="flex gap-0.5 overflow-hidden rounded-full">
                    @for ($i = 0; $i < 10; $i++)
                        @php
                            if ($currentTierIndex === 9) {
                                $segClass = 'bg-blue-400 dark:bg-blue-500';
                                $segWidth = 'flex-1';
                            } elseif ($i < $currentTierIndex) {
                                $segClass = 'bg-blue-400 dark:bg-blue-500';
                                $segWidth = 'flex-1';
                            } elseif ($i === $currentTierIndex || ($i === 0 && $currentTierIndex === -1)) {
                                $segClass = 'relative overflow-hidden bg-blue-100 dark:bg-blue-900/40 flex-1';
                                $segWidth = 'flex-1';
                            } else {
                                $segClass = 'bg-blue-100/70 dark:bg-blue-900/20';
                                $segWidth = 'flex-1';
                            }
                        @endphp
                        <div class="h-5 min-w-0 {{ $segWidth }} {{ $segClass }} first:rounded-l-full last:rounded-r-full">
                            @if (($i === $currentTierIndex && $currentTierIndex >= 0) || ($i === 0 && $currentTierIndex === -1))
                                <div class="h-full bg-gradient-to-r from-blue-400 to-indigo-400 dark:from-blue-500 dark:to-indigo-400" style="width: {{ $wealthProgress }}%"></div>
                            @endif
                        </div>
                    @endfor
                </div>

                {{-- Kademe etiketleri --}}
                <div class="flex">
                    @for ($i = 0; $i < 10; $i++)
                        <div class="flex-1 text-center">
                            <span class="text-[10px] font-medium {{ $i <= $currentTierIndex ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-600' }}">
                                {{ $romanNumerals[$i] }}
                            </span>
                        </div>
                    @endfor
                </div>
            </div>
        </section>

        {{-- SATIR 3: Yapılacaklar (AJAX) + Bucketlist --}}
        <section class="grid gap-4 md:grid-cols-2">

            <article
                class="card-admin"
                x-data="{
                    todos: {{ Js::from($dueTodos->map(fn($t) => [
                        'id'       => $t->id,
                        'title'    => $t->title,
                        'due_date' => $t->due_date?->format('d M'),
                        'completed'=> false,
                    ])) }},
                    async toggle(todo) {
                        const res = await fetch(`{{ url('/admin/todo-items') }}/${todo.id}/toggle-complete`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();
                        todo.completed = data.completed;
                    }
                }"
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Bugün / Gecikmiş</h3>
                    <a href="{{ route('admin.todo-items.index', ['filter' => 'due']) }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                        @if ($dueTodosTotal > 10)Tümü ({{ $dueTodosTotal }})@else Yönet @endif
                    </a>
                </div>

                <div class="mt-3 space-y-2">
                    <template x-for="todo in todos" :key="todo.id">
                        <div
                            class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 transition dark:border-gray-700"
                            :class="todo.completed ? 'opacity-50' : ''"
                        >
                            <button
                                type="button"
                                @click="toggle(todo)"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition"
                                :class="todo.completed
                                    ? 'border-emerald-500 bg-emerald-500 text-white'
                                    : 'border-gray-300 hover:border-emerald-400 dark:border-gray-600'"
                            >
                                <svg x-show="todo.completed" class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                                </svg>
                            </button>
                            <span
                                class="flex-1 text-sm text-gray-800 dark:text-gray-100"
                                :class="todo.completed ? 'line-through text-gray-400' : ''"
                                x-text="todo.title"
                            ></span>
                            <span class="shrink-0 text-xs text-gray-400" x-text="todo.due_date"></span>
                        </div>
                    </template>

                    @if ($dueTodos->isEmpty())
                        <p class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-sm text-gray-400 dark:border-gray-700">
                            Bekleyen görev yok 🎉
                        </p>
                    @endif
                </div>
            </article>

            @php
                $bucketlistPct = $bucketlistTotal > 0
                    ? min(100, (int) (($bucketlistCompleted / $bucketlistTotal) * 100))
                    : 0;
            @endphp
            <article class="card-admin border border-purple-200/70 bg-gradient-to-br from-purple-50 to-fuchsia-50 dark:border-purple-800/60 dark:from-purple-950/30 dark:to-fuchsia-950/30">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Bucket List</h3>
                    <a href="{{ route('admin.bucketlist') }}" class="text-xs font-medium text-purple-600 hover:underline dark:text-purple-400">Görüntüle</a>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-extrabold text-purple-700 dark:text-purple-300">{{ $bucketlistCompleted }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">/ {{ $bucketlistTotal }} tamamlandı</p>
                    </div>
                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-300">%{{ $bucketlistPct }}</span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-purple-100 dark:bg-purple-900/40">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 transition-all"
                        style="width: {{ $bucketlistPct }}%"
                    ></div>
                </div>
                @if ($bucketlistTotal === 0)
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                        Henüz bucket list'e eklenen öğe yok.
                        <a href="{{ route('admin.todo-items.create') }}" class="text-purple-600 hover:underline dark:text-purple-400">Ekle →</a>
                    </p>
                @endif
            </article>

        </section>

        {{-- SATIR 4: Bugünkü Beslenme --}}
        <section class="card-admin">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Bugünkü Beslenme') }}</h3>
                <a href="{{ route('admin.consumptions.index') }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">{{ __('Yönet') }}</a>
            </div>
            <div class="mt-3 flex items-center gap-6">
                <div class="relative h-28 w-28 shrink-0">
                    <canvas id="nutritionPieChart"></canvas>
                </div>
                <div class="space-y-1 text-sm">
                    <p class="text-lg font-extrabold text-gray-800 dark:text-gray-100">{{ number_format($dailyCalories, 0) }} kcal</p>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Karbonhidrat') }}: {{ number_format($dailyCarbs, 1) }} g</p>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Yağ') }}: {{ number_format($dailyFat, 1) }} g</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('Şeker') }}: {{ number_format($dailySugar, 1) }} g</p>
                </div>
            </div>
        </section>

    </div>
    {{-- / KİŞİSEL DASHBOARD --}}

    <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('nutritionPieChart');
            if (ctx && window.Chart) {
                new window.Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ __('Karbonhidrat') }}', '{{ __('Yağ') }}'],
                        datasets: [{
                            data: [{{ $dailyCarbs }}, {{ $dailyFat }}],
                            backgroundColor: ['#3B82F6', '#F97316'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        cutout: '65%',
                    },
                });
            }
        });
    </script>

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

        @php
            $daysAlive = (int) \Illuminate\Support\Carbon::parse('1990-03-21')->diffInDays(now());
        @endphp
        <section x-cloak x-show="!loading" class="card-admin border border-rose-200/70 bg-gradient-to-r from-rose-50 to-pink-50 dark:border-rose-800/70 dark:from-rose-950/30 dark:to-pink-950/30">
            <p class="text-sm font-medium text-rose-700 dark:text-rose-300 text-center">
                <span class="text-2xl font-extrabold tabular-nums">{{ number_format($daysAlive) }}</span>
                gündür hayattasın.
            </p>
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
                        <a href="{{ route('admin.people.show', $person->id) }}" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-900/60">
                            <img
                                src="{{ $person->pictureUrl }}"
                                alt="{{ trim(($person->name ?? '') . ' ' . ($person->surname ?? '')) }}"
                                class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                            >
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ trim(($person->name ?? '') . ' ' . ($person->surname ?? '')) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($person->closest_birthday)->translatedFormat('d M') }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold {{ $daysFromToday === 0 ? 'text-rose-600 dark:text-rose-300' : 'text-gray-500 dark:text-gray-300' }}">
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
