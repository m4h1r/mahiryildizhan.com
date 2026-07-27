@extends('admin.layout', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
    @php
        // ── Shared derived values (computed once, reused across sections) ──
        $fxUsdTry = data_get($rates, 'fx.rates.USD') ? 1 / (float) data_get($rates, 'fx.rates.USD') : null;
        $fxEurTry = data_get($rates, 'fx.rates.EUR') ? 1 / (float) data_get($rates, 'fx.rates.EUR') : null;
        $fxGbpTry = data_get($rates, 'fx.rates.GBP') ? 1 / (float) data_get($rates, 'fx.rates.GBP') : null;
        $btcUsd = (float) data_get($rates, 'crypto.bitcoin.usd', 0);
        $ethUsd = (float) data_get($rates, 'crypto.ethereum.usd', 0);

        $annualNet = $annualIncome - $annualExpense;
        $isAnnualNetNegative = $annualNet < 0;
        $isNetNegative = $monthlyNet < 0;

        $daysAlive = (int) \Illuminate\Support\Carbon::parse('1990-03-21')->diffInDays(now());

        $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

        $bucketlistPct = $bucketlistTotal > 0
            ? min(100, (int) (($bucketlistCompleted / $bucketlistTotal) * 100))
            : 0;

        $dailyTimes = $weather['daily']['time'] ?? [];
        $dailyMin = $weather['daily']['temperature_2m_min'] ?? [];
        $dailyMax = $weather['daily']['temperature_2m_max'] ?? [];
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

    <div class="space-y-6" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)">

        {{-- =====================================================
             1) BUGÜN — saat + hava durumu + beslenme (üst grup)
        ===================================================== --}}
        <section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">

            {{-- Günün Saati — 24 saatlik neon kadran --}}
            <article class="card-admin flex flex-col items-center justify-center gap-3 p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('Günün Saati') }}</h2>

                <div class="relative flex h-40 w-40 items-center justify-center">
                    <div class="absolute bottom-1 left-1/2 h-4 w-24 -translate-x-1/2 rounded-full bg-black/25 blur-lg dark:bg-black/55" aria-hidden="true"></div>

                    <div
                        class="clock-neon-ring relative h-36 w-36 rounded-full"
                        style="
                            background: conic-gradient({{ $clockRing['gradient'] }});
                            box-shadow:
                                0 0 12px 1px {{ $clockRing['currentColor'] }}99,
                                0 0 28px 6px {{ $clockRing['currentColor'] }}4D;
                        "
                    >
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

                        <span class="absolute left-1/2 top-2 -translate-x-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">00</span>
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">06</span>
                        <span class="absolute bottom-2 left-1/2 -translate-x-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">12</span>
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-[#EEF2F9]/95 [text-shadow:0_1px_2px_rgb(0_0_0/0.18)] dark:text-slate-200/80">18</span>
                    </div>
                </div>

                <a href="{{ route('admin.settings', ['tab' => 'time_ranges']) }}" class="text-xs text-gray-400 hover:underline dark:text-gray-500">{{ __('Zaman aralıklarını düzenle →') }}</a>
            </article>

            {{-- Hava durumu (5 gün) + yaşam sayacı --}}
            <article class="card-admin lg:col-span-1 xl:col-span-2 border border-blue-200/80 bg-gradient-to-br from-blue-50 to-indigo-50 dark:border-blue-800 dark:from-blue-950/40 dark:to-indigo-950/40">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $weatherCityName }} {{ __('Weather') }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('5-day outlook') }}</p>
                    </div>
                    <p class="shrink-0 text-right text-xs text-rose-600 dark:text-rose-300">
                        <span class="text-lg font-extrabold tabular-nums">{{ number_format($daysAlive) }}</span><br>
                        {{ __('gündür hayattasın') }}
                    </p>
                </div>

                @if (!empty($dailyTimes))
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach (array_slice($dailyTimes, 0, 5) as $index => $date)
                            @php
                                $code = (int) ($weather['daily']['weathercode'][$index] ?? -1);
                                $icon = $weatherCodeToIcon[$code] ?? '🌤️';
                            @endphp
                            <div class="rounded-xl border border-blue-200/70 bg-white/80 p-3 text-center shadow-sm transition hover:shadow-md dark:border-blue-800/60 dark:bg-gray-900/50">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Carbon::parse($date)->format('D, d M') }}</p>
                                <p class="mt-2 text-3xl">{{ $icon }}</p>
                                <p class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $dailyMax[$index] ?? '-' }}° / {{ $dailyMin[$index] ?? '-' }}°
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Weather data unavailable right now.') }}</p>
                @endif
            </article>

            {{-- Beslenme — bugün / dün geçişli --}}
            <article
                class="card-admin lg:col-span-2 xl:col-span-1"
                x-data="{
                    day: 'today',
                    nutrition: {{ Js::from($nutrition) }},
                    goals: { calorie: {{ $calorieGoal }}, carbs: {{ $carbsGoal }}, protein: {{ $proteinGoal }}, fat: {{ $fatGoal }} },
                    chart: null,
                    get d() { return this.nutrition[this.day]; },
                    barClass() { return { danger: 'bg-red-500', warning: 'bg-amber-500', success: 'bg-green-500' }[this.d.status]; },
                    textClass() {
                        return {
                            danger: 'text-red-600 dark:text-red-400',
                            warning: 'text-amber-600 dark:text-amber-400',
                            success: 'text-green-600 dark:text-green-400',
                        }[this.d.status];
                    },
                    fmt(v, dec = 0) { return Number(v).toLocaleString('tr-TR', { minimumFractionDigits: dec, maximumFractionDigits: dec }); },
                    init() {
                        this.$nextTick(() => {
                            const ctx = this.$refs.chart;
                            if (!ctx || !window.Chart) return;
                            this.chart = new window.Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['{{ __('Karbonhidrat') }}', '{{ __('Yağ') }}', '{{ __('Protein') }}'],
                                    datasets: [{
                                        data: [this.d.carbs, this.d.fat, this.d.protein],
                                        backgroundColor: ['#3B82F6', '#F97316', '#8B5CF6'],
                                        borderWidth: 0,
                                    }],
                                },
                                options: { plugins: { legend: { display: false } }, cutout: '65%' },
                            });
                        });
                    },
                    setDay(d) {
                        this.day = d;
                        if (this.chart) {
                            this.chart.data.datasets[0].data = [this.d.carbs, this.d.fat, this.d.protein];
                            this.chart.update();
                        }
                    },
                }"
            >
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <span x-text="day === 'today' ? '{{ __('Bugünkü Beslenme') }}' : '{{ __('Dünkü Beslenme') }}'"></span>
                    </h3>
                    <a href="{{ route('admin.consumptions.index') }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">{{ __('Yönet') }}</a>
                </div>

                <div class="mt-3 inline-flex rounded-lg border border-gray-200 p-0.5 text-xs font-medium dark:border-gray-700">
                    <button type="button" @click="setDay('today')"
                        class="rounded-md px-3 py-1 transition"
                        :class="day === 'today' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                        {{ __('Bugün') }}
                    </button>
                    <button type="button" @click="setDay('yesterday')"
                        class="rounded-md px-3 py-1 transition"
                        :class="day === 'yesterday' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                        {{ __('Dün') }}
                    </button>
                </div>

                <div class="mt-3 flex items-center gap-4">
                    <div class="relative h-28 w-28 shrink-0">
                        <canvas x-ref="chart"></canvas>
                    </div>
                    <div class="space-y-1 text-sm">
                        <p class="text-lg font-extrabold text-gray-800 dark:text-gray-100"><span x-text="fmt(d.calories)"></span> kcal</p>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Karbonhidrat') }}: <span x-text="fmt(d.carbs, 1)"></span> g <span class="text-xs text-gray-400 dark:text-gray-500">(<span x-text="fmt(goals.carbs)"></span>g)</span></p>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Protein') }}: <span x-text="fmt(d.protein, 1)"></span> g <span class="text-xs text-gray-400 dark:text-gray-500">(<span x-text="fmt(goals.protein)"></span>g)</span></p>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Yağ') }}: <span x-text="fmt(d.fat, 1)"></span> g <span class="text-xs text-gray-400 dark:text-gray-500">(<span x-text="fmt(goals.fat)"></span>g)</span></p>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-500 dark:text-gray-400">{{ __('Kalori Hedefi') }}</span>
                        <span class="font-semibold" :class="textClass()"><span x-text="fmt(d.calories)"></span> / <span x-text="fmt(goals.calorie)"></span> kcal</span>
                    </div>
                    <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full transition-all duration-500" :class="barClass()" :style="`width: ${d.percent}%`"></div>
                    </div>
                </div>
            </article>
        </section>

        {{-- =====================================================
             3) FİNANS — tüm para tek bölümde (yıllık net TEK KEZ)
        ===================================================== --}}
        <section class="card-admin">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Financial Status') }} · {{ now()->year }}</h3>
                <a href="{{ route('admin.reports') }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">{{ __('Open Financial Reports') }}</a>
            </div>

            {{-- Kasa + aylık net + yıllık gelir/gider/net tek satır --}}
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 to-teal-50 p-3 dark:border-emerald-800/60 dark:from-emerald-950/30 dark:to-teal-950/30">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Kasa') }}</p>
                    <p class="mt-1 text-xl font-extrabold text-emerald-700 dark:text-emerald-300">₺{{ number_format($treasuryTry, 2) }}</p>
                    @if ($treasuryUsd !== null)
                        <p class="text-xs text-gray-400 dark:text-gray-500">≈ ${{ number_format($treasuryUsd, 0) }}</p>
                    @endif
                    <a href="{{ route('admin.settings') }}" class="text-xs text-gray-400 hover:underline dark:text-gray-500">{{ __('Güncelle') }} →</a>
                </div>
                <div class="rounded-xl border {{ $isNetNegative ? 'border-red-200/70' : 'border-emerald-200/70' }} bg-gradient-to-br from-sky-50 to-blue-50 p-3 dark:border-gray-700 dark:from-sky-950/30 dark:to-blue-950/30">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Monthly Net') }}</p>
                    <p class="mt-1 text-xl font-extrabold {{ $isNetNegative ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($monthlyNet, 2) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">TRY · {{ now()->translatedFormat('F') }}</p>
                </div>
                <div class="rounded-xl border border-green-200/70 bg-white/70 p-3 dark:border-green-800/50 dark:bg-gray-900/40">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Income') }}</p>
                    <p class="mt-1 text-xl font-extrabold text-green-700 dark:text-green-300">{{ number_format($annualIncome, 2) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">TRY</p>
                </div>
                <div class="rounded-xl border border-red-200/70 bg-white/70 p-3 dark:border-red-800/50 dark:bg-gray-900/40">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Expense') }}</p>
                    <p class="mt-1 text-xl font-extrabold text-red-700 dark:text-red-300">{{ number_format($annualExpense, 2) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">TRY</p>
                </div>
                <div class="rounded-xl border {{ $isAnnualNetNegative ? 'border-red-200/70' : 'border-emerald-200/70' }} bg-white/70 p-3 dark:bg-gray-900/40 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Net') }}</p>
                    <p class="mt-1 text-xl font-extrabold {{ $isAnnualNetNegative ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ number_format($annualNet, 2) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">TRY</p>
                </div>
            </div>

            {{-- FX / Kripto şeridi --}}
            <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-3 lg:grid-cols-5 dark:border-gray-800">
                <div class="rounded-lg border border-green-200 bg-gradient-to-br from-green-50 to-green-100 p-3 dark:border-green-800 dark:from-green-950/40 dark:to-green-900/40">
                    <p class="text-xs text-gray-600 dark:text-gray-300">USD/TRY</p>
                    <p class="mt-1 text-lg font-bold text-green-700 dark:text-green-300">{{ $fxUsdTry ? number_format($fxUsdTry, 4) : '-' }}</p>
                </div>
                <div class="rounded-lg border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-3 dark:border-indigo-800 dark:from-indigo-950/40 dark:to-indigo-900/40">
                    <p class="text-xs text-gray-600 dark:text-gray-300">EUR/TRY</p>
                    <p class="mt-1 text-lg font-bold text-indigo-700 dark:text-indigo-300">{{ $fxEurTry ? number_format($fxEurTry, 4) : '-' }}</p>
                </div>
                <div class="hidden rounded-lg border border-violet-200 bg-gradient-to-br from-violet-50 to-violet-100 p-3 md:block dark:border-violet-800 dark:from-violet-950/40 dark:to-violet-900/40">
                    <p class="text-xs text-gray-600 dark:text-gray-300">GBP/TRY</p>
                    <p class="mt-1 text-lg font-bold text-violet-700 dark:text-violet-300">{{ $fxGbpTry ? number_format($fxGbpTry, 4) : '-' }}</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100 p-3 dark:border-amber-800 dark:from-amber-950/40 dark:to-amber-900/40">
                    <p class="text-xs text-gray-600 dark:text-gray-300">BTC/USD</p>
                    <p class="mt-1 text-lg font-bold text-amber-700 dark:text-amber-300">${{ number_format($btcUsd, 2) }}</p>
                </div>
                <div class="rounded-lg border border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100 p-3 dark:border-blue-800 dark:from-blue-950/40 dark:to-blue-900/40">
                    <p class="text-xs text-gray-600 dark:text-gray-300">ETH/USD</p>
                    <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-300">${{ number_format($ethUsd, 2) }}</p>
                </div>
            </div>

            {{-- Zenginlik Seviyesi + Günlük Pasif Gelir (tam genişlik) --}}
            <div class="mt-4 rounded-xl border border-blue-200/80 bg-gradient-to-br from-blue-50 to-indigo-50 p-4 dark:border-blue-800/60 dark:from-blue-950/30 dark:to-indigo-950/30">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Zenginlik Seviyesi') }}</p>
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

                    {{-- Günlük pasif gelir — kademeyi besleyen değer --}}
                    <div class="rounded-lg bg-white/70 px-3 py-2 text-right dark:bg-gray-900/50">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Günlük Pasif Gelir') }}</p>
                        <p class="text-lg font-extrabold text-sky-700 dark:text-sky-300">₺{{ number_format($dailyPassiveIncomeTry, 2) }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400">/gün</span></p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            ₺{{ number_format($monthlyPassiveIncomeTry, 0) }}/ay
                            @if ($monthlyPassiveIncomeUsd > 0)
                                · ≈ ${{ number_format($monthlyPassiveIncomeUsd, 0) }}/ay
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Segmentli 10-kademe progress bar --}}
                <div class="space-y-2">
                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">İlerleme</span>
                        <span class="rounded-lg bg-blue-100 px-2.5 py-1 text-sm font-extrabold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">%{{ number_format($wealthProgress, 2) }}</span>
                    </div>
                    <div class="flex gap-0.5 overflow-hidden rounded-full">
                        @for ($i = 0; $i < 10; $i++)
                            @php
                                if ($currentTierIndex === 9) {
                                    $segClass = 'bg-blue-400 dark:bg-blue-500';
                                } elseif ($i < $currentTierIndex) {
                                    $segClass = 'bg-blue-400 dark:bg-blue-500';
                                } elseif ($i === $currentTierIndex || ($i === 0 && $currentTierIndex === -1)) {
                                    $segClass = 'relative overflow-hidden bg-blue-100 dark:bg-blue-900/40';
                                } else {
                                    $segClass = 'bg-blue-100/70 dark:bg-blue-900/20';
                                }
                            @endphp
                            <div class="h-5 min-w-0 flex-1 {{ $segClass }} first:rounded-l-full last:rounded-r-full">
                                @if (($i === $currentTierIndex && $currentTierIndex >= 0) || ($i === 0 && $currentTierIndex === -1))
                                    <div class="h-full bg-gradient-to-r from-blue-400 to-indigo-400 dark:from-blue-500 dark:to-indigo-400" style="width: {{ $wealthProgress }}%"></div>
                                @endif
                            </div>
                        @endfor
                    </div>
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
            </div>
        </section>

        {{-- =====================================================
             4) KİŞİSEL TAKİP — bucket list + bugün/gecikmiş (yan yana)
        ===================================================== --}}
        <section class="grid gap-4 xl:grid-cols-2">

            {{-- Bucket List --}}
            <article class="card-admin border border-purple-200/70 bg-gradient-to-br from-purple-50 to-fuchsia-50 dark:border-purple-800/60 dark:from-purple-950/30 dark:to-fuchsia-950/30">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Bucket List') }}</h3>
                    <a href="{{ route('admin.bucketlist') }}" class="text-xs font-medium text-purple-600 hover:underline dark:text-purple-400">{{ __('Görüntüle') }}</a>
                </div>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-extrabold text-purple-700 dark:text-purple-300">{{ $bucketlistCompleted }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">/ {{ $bucketlistTotal }} tamamlandı</p>
                    </div>
                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-300">%{{ $bucketlistPct }}</span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-purple-100 dark:bg-purple-900/40">
                    <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 transition-all" style="width: {{ $bucketlistPct }}%"></div>
                </div>
                @if ($bucketlistTotal === 0)
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                        Henüz bucket list'e eklenen öğe yok.
                        <a href="{{ route('admin.todo-items.create') }}" class="text-purple-600 hover:underline dark:text-purple-400">Ekle →</a>
                    </p>
                @endif
            </article>

            {{-- Bugün / Gecikmiş yapılacaklar (AJAX toggle) --}}
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
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Bugün / Gecikmiş') }}</h3>
                    <a href="{{ route('admin.todo-items.index', ['filter' => 'due']) }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                        @if ($dueTodosTotal > 10)Tümü ({{ $dueTodosTotal }})@else {{ __('Yönet') }} @endif
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
        </section>

        {{-- =====================================================
             6) İÇERİK & İNSANLAR — yazılar + yorumlar + doğum günleri
        ===================================================== --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">

            {{-- Son yazılar --}}
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

            {{-- Son yorumlar (başlıkta onaylı rozeti) --}}
            <article class="card-admin">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Recent Comments') }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('Approved') }}: {{ number_format($approvedComments) }}</span>
                        <a href="{{ route('admin.comments.index') }}" class="text-xs font-medium text-blue-700 hover:underline dark:text-blue-300">{{ __('Moderate') }}</a>
                    </div>
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

            {{-- Bu haftaki doğum günleri --}}
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
        </section>

        {{-- =====================================================
             7) HIZLI İŞLEMLER — ince alt bar
        ===================================================== --}}
        <section class="card-admin">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Quick Actions') }}</h3>
                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                    <a href="{{ route('admin.posts.create') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Create New Post') }}</a>
                    <a href="{{ route('admin.people.create') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Add New Person') }}</a>
                    <a href="{{ route('admin.reports') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Open Financial Reports') }}</a>
                    <a href="{{ route('admin.import.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900">{{ __('Run CSV Import') }}</a>
                </div>
            </div>
        </section>
    </div>

@endsection
