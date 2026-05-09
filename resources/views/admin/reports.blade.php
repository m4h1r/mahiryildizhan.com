@extends('admin.layout', ['title' => __('Reports'), 'heading' => __('Reports')])

@section('content')
    @php
        $availableYears = range(now()->year, max(now()->year - 10, 2015));
        if (! in_array($year, $availableYears, true)) {
            $availableYears[] = $year;
        }
        rsort($availableYears);

        $topRows    = collect($expenseTypeRows)->sortByDesc('total')->take(5)->values();
        $netPositive = $netBalance >= 0;

        // Build GitHub-style heatmap grid
        $hmYearStart  = \Illuminate\Support\Carbon::create($year, 1, 1);
        $hmYearEnd    = \Illuminate\Support\Carbon::create($year, 12, 31);
        $hmGridStart  = $hmYearStart->copy()->startOfWeek(1); // Monday
        $hmGridEnd    = $hmYearEnd->copy()->endOfWeek(0);     // Sunday
        $hmCursor     = $hmGridStart->copy();
        $hmWeeks      = [];
        $hmMonthLabels = [];
        $seenHmMonths = [];
        $hmWeekIndex  = 0;

        while ($hmCursor->lte($hmGridEnd)) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dateStr  = $hmCursor->toDateString();
                $inYear   = (int) $hmCursor->format('Y') === $year;
                $monthNum = (int) $hmCursor->format('n');

                if ($inYear && ! in_array($monthNum, $seenHmMonths)) {
                    $seenHmMonths[]           = $monthNum;
                    $hmMonthLabels[$hmWeekIndex] = $hmCursor->format('M');
                }

                $week[] = [
                    'date'    => $dateStr,
                    'in_year' => $inYear,
                    'total'   => $inYear ? ($heatmapByDate[$dateStr]['total'] ?? 0) : 0,
                    'level'   => $inYear ? ($heatmapByDate[$dateStr]['level'] ?? 0) : -1,
                    'label'   => $hmCursor->format('d M Y'),
                ];
                $hmCursor->addDay();
            }
            $hmWeeks[] = $week;
            $hmWeekIndex++;
        }

        $hmLevelClasses = [
            -1 => '',
            0  => 'bg-gray-100 dark:bg-gray-800',
            1  => 'bg-rose-100 dark:bg-rose-900/70',
            2  => 'bg-rose-300 dark:bg-rose-700',
            3  => 'bg-rose-500 dark:bg-rose-500',
            4  => 'bg-rose-700 dark:bg-rose-300',
        ];
        $hmDayLabels  = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
        $catHmMonths  = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'),
                         __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')];
    @endphp

    <div class="space-y-6">

        {{-- ── Hero header ── --}}
        <section class="relative overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 px-6 py-6 text-white shadow-xl md:px-8 md:py-8">
            <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-cyan-300/15 blur-2xl"></div>
            <div class="absolute -bottom-16 left-24 h-48 w-48 rounded-full bg-indigo-300/15 blur-2xl"></div>

            <div class="relative space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200/90">{{ __('Financial Intelligence') }}</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">{{ __('Reports Command Center') }} · {{ $year }}</h2>
                    <p class="mt-2 max-w-2xl text-sm text-cyan-100/80">{{ __('Monthly performance, spend composition, trend pressure, and category-level signals in one board.') }}</p>
                </div>

                <form method="GET" class="grid max-w-fit gap-2 rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur sm:grid-cols-2 lg:grid-cols-3">
                    <label class="text-xs font-medium text-cyan-100">
                        <span class="mb-1 block">{{ __('Year') }}</span>
                        <select name="year" class="form-input-admin !border-white/20 !bg-white/10 !text-white">
                            @foreach ($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" @selected($availableYear === $year)>{{ $availableYear }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-medium text-cyan-100">
                        <span class="mb-1 block">{{ __('Daily Range') }}</span>
                        <select name="daily_range" class="form-input-admin !border-white/20 !bg-white/10 !text-white">
                            <option value="week"  @selected($dailyRange === 'week')>{{ __('Last 7 days') }}</option>
                            <option value="month" @selected($dailyRange === 'month')>{{ __('Last 30 days') }}</option>
                            <option value="ytd"   @selected($dailyRange === 'ytd')>{{ __('Year to date') }}</option>
                            <option value="year"  @selected($dailyRange === 'year')>{{ __('Full year') }}</option>
                        </select>
                    </label>
                    <button type="submit" class="admin-btn admin-btn-primary sm:col-span-2 lg:col-span-1">{{ __('Apply') }}</button>
                </form>
            </div>
        </section>

        {{-- ── Summary cards ── --}}
        <section class="grid gap-4 md:grid-cols-2">
            <article class="card-admin">
                <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Financial Summary') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="border-l-4 border-emerald-500 pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Income') }}</p>
                        <p class="mt-1 text-xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($annualIncome, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Avg:') }} {{ number_format($insights['avg_monthly_income'], 2) }}</p>
                    </div>
                    <div class="border-l-4 border-rose-500 pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Expense') }}</p>
                        <p class="mt-1 text-xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($annualExpense, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Avg:') }} {{ number_format($insights['avg_monthly_expense'], 2) }}</p>
                    </div>
                    <div class="border-l-4 {{ $netPositive ? 'border-cyan-500' : 'border-amber-500' }} pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Net') }}</p>
                        <p class="mt-1 text-xl font-semibold {{ $netPositive ? 'text-cyan-700 dark:text-cyan-300' : 'text-amber-700 dark:text-amber-300' }}">{{ number_format($netBalance, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Save:') }} {{ number_format($insights['savings_rate'], 1) }}%</p>
                    </div>
                </div>
            </article>
            <article class="card-admin border-l-4 border-violet-500">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Peak Spend Month') }}</p>
                <p class="mt-1 text-2xl font-semibold text-violet-700 dark:text-violet-300">{{ $insights['top_expense_month']['label'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($insights['top_expense_month']['value'], 2) }} TRY</p>
            </article>
        </section>

        {{-- ── Monthly chart + donut ── --}}
        <section class="grid gap-6 xl:grid-cols-3">
            <article class="card-admin xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Income vs Expense vs Net') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Monthly performance timeline') }}</p>
                </div>
                <div class="h-[16rem] sm:h-[22rem]">
                    <canvas id="monthlyPerformanceChart"></canvas>
                </div>
            </article>

            <article class="card-admin">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Expense Distribution') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('By category') }}</p>
                </div>
                <div class="h-[16rem] sm:h-[22rem]">
                    <canvas id="expenseShareChart"></canvas>
                </div>
            </article>
        </section>

        {{-- ── Daily pressure + cumulative ── --}}
        <section class="grid gap-6 xl:grid-cols-5">
            <article class="card-admin xl:col-span-3">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Daily Expense Pressure') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Rolling daily totals') }}</p>
                </div>
                <div class="h-[16rem] sm:h-[21rem]">
                    <canvas id="dailyExpenseChart"></canvas>
                </div>
            </article>

            <article class="card-admin xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Cumulative Net Curve') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Running result across months') }}</p>
                </div>
                <div class="h-[16rem] sm:h-[21rem]">
                    <canvas id="cumulativeNetChart"></canvas>
                </div>
            </article>
        </section>

        {{-- ── Category trend ── --}}
        <section class="card-admin">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold">{{ __('Category Trend Signals') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Top categories over selected period') }}</p>
            </div>
            <div class="h-[18rem] sm:h-[24rem]">
                <canvas id="categoryTrendChart"></canvas>
            </div>
        </section>

        {{-- ── Top-5 spend cards ── --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @forelse ($topRows as $row)
                <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/50">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</p>
                    <p class="mt-1 text-lg font-semibold text-rose-700 dark:text-rose-300">{{ number_format($row['total'], 2) }} TRY</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Daily:') }} {{ number_format($row['daily_avg'], 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Monthly:') }} {{ number_format($row['monthly_avg'], 2) }}</p>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No TRY expense data for this period.') }}</p>
            @endforelse
        </section>

        {{-- ── Heatmaps ── --}}
        <section class="grid gap-6 xl:grid-cols-2">

            {{-- Daily calendar heatmap --}}
            <article class="card-admin">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Expense Heatmap') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Daily expenses across the year') }}</p>
                </div>

                @if(empty($heatmapByDate))
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No TRY expense data for this year.') }}</p>
                @else
                    <div class="overflow-x-auto pb-1">
                        <div class="inline-flex gap-2">
                            <div class="flex shrink-0 flex-col gap-[3px] pt-5">
                                @foreach($hmDayLabels as $i => $dayLabel)
                                    <div class="flex h-3 items-center text-[9px] leading-none text-gray-400 dark:text-gray-500">
                                        {{ in_array($i, [0, 2, 4]) ? $dayLabel : '' }}
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex flex-col gap-0">
                                <div class="mb-1 flex gap-[3px]">
                                    @foreach($hmWeeks as $wi => $week)
                                        <div class="w-3 shrink-0 text-[9px] leading-none text-gray-400 dark:text-gray-500">{{ $hmMonthLabels[$wi] ?? '' }}</div>
                                    @endforeach
                                </div>
                                @for($dayIndex = 0; $dayIndex < 7; $dayIndex++)
                                    <div class="mb-[3px] flex gap-[3px]">
                                        @foreach($hmWeeks as $week)
                                            @php $cell = $week[$dayIndex]; @endphp
                                            <div class="h-3 w-3 shrink-0 rounded-[2px] {{ $hmLevelClasses[$cell['level']] }}"
                                                 @if($cell['in_year'] && $cell['total'] > 0) title="{{ $cell['label'] }}: ₺{{ number_format($cell['total'], 0) }}" @endif></div>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-500">
                        <span>{{ __('Less') }}</span>
                        @foreach([0, 1, 2, 3, 4] as $lvl)
                            <div class="h-3 w-3 rounded-[2px] {{ $hmLevelClasses[$lvl] }}"></div>
                        @endforeach
                        <span>{{ __('More') }}</span>
                    </div>
                @endif
            </article>

            {{-- Category × month heatmap --}}
            <article class="card-admin">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Category Heatmap') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Monthly spend by top categories') }}</p>
                </div>

                @if(empty($categoryHeatmap))
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No TRY expense data for this year.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <canvas id="categoryHeatmapCanvas"></canvas>
                    </div>
                @endif
            </article>

            {{-- Income Category × month heatmap --}}
            <article class="card-admin">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">{{ __('Income Category Heatmap') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Monthly income by top categories') }}</p>
                </div>

                @if(empty($incomeCategoryHeatmap))
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No TRY income data for this year.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <canvas id="incomeCategoryHeatmapCanvas"></canvas>
                    </div>
                @endif
            </article>

        </section>

        {{-- ── Type analysis table ── --}}
        <section class="card-admin overflow-x-auto">
            <h3 class="px-4 pt-4 text-sm font-semibold">{{ __('Expense Type Analysis Table') }}</h3>
            <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">{{ __('Type') }}</th>
                        <th class="px-3 py-2 text-left font-medium">{{ __('Total') }}</th>
                        <th class="px-3 py-2 text-left font-medium">{{ __('Daily Avg') }}</th>
                        <th class="px-3 py-2 text-left font-medium">{{ __('Monthly Avg') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($expenseTypeRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ number_format($row['total'], 2) }}</td>
                            <td class="px-3 py-2">{{ number_format($row['daily_avg'], 2) }}</td>
                            <td class="px-3 py-2">{{ number_format($row['monthly_avg'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ __('No TRY expense data for this year.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) {
                return;
            }

            const monthly              = @json($monthly);
            const monthlyExpenseTotals = @json($monthlyExpenseTotals);
            const monthlyNet           = @json($monthlyNet);
            const cumulativeNet        = @json($cumulativeNet);
            const expenseTypeChart     = @json($expenseTypeChart);
            const dailyLabels          = @json($dailyExpenseLabels);
            const dailyTotals          = @json($dailyExpenseTotals);
            const categorySeries       = @json($dailyCategoryData);
            const categoryHeatmapData       = @json($categoryHeatmap);
            const incomeCategoryHeatmapData = @json($incomeCategoryHeatmap);
            const catMonthLabels            = @json($catHmMonths);
            const isDark               = document.documentElement.classList.contains('dark');
            const axisColor            = isDark ? '#94a3b8' : '#64748b';
            const gridColor            = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(100,116,139,0.16)';

            const createChart = function (id, config) {
                const element = document.getElementById(id);
                if (!element) { return null; }
                const context = element.getContext('2d');
                if (!context) { return null; }
                return new window.Chart(context, config);
            };

            createChart('monthlyPerformanceChart', {
                type: 'bar',
                data: {
                    labels: monthly.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: @json(__('Income')),
                            data: monthly.income,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderRadius: 6,
                        },
                        {
                            type: 'bar',
                            label: @json(__('Expense')),
                            data: monthlyExpenseTotals,
                            backgroundColor: 'rgba(244, 63, 94, 0.7)',
                            borderRadius: 6,
                        },
                        {
                            type: 'line',
                            label: @json(__('Net')),
                            data: monthlyNet,
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6, 182, 212, 0.2)',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.35,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: axisColor }, grid: { color: gridColor } },
                        y: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    },
                    plugins: { legend: { labels: { color: axisColor } } },
                },
            });

            createChart('expenseShareChart', {
                type: 'doughnut',
                data: {
                    labels: expenseTypeChart.labels,
                    datasets: [{
                        data: expenseTypeChart.values,
                        backgroundColor: [
                            '#2563eb', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                            '#14b8a6', '#f97316', '#ec4899', '#06b6d4', '#84cc16',
                        ],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: axisColor } } },
                },
            });

            createChart('dailyExpenseChart', {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: @json(__('Daily Expense')),
                        data: dailyTotals,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.22)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: axisColor, maxRotation: 0 }, grid: { color: gridColor } },
                        y: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    },
                    plugins: { legend: { labels: { color: axisColor } } },
                },
            });

            createChart('cumulativeNetChart', {
                type: 'line',
                data: {
                    labels: monthly.labels,
                    datasets: [{
                        label: @json(__('Cumulative Net')),
                        data: cumulativeNet,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.18)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: axisColor }, grid: { color: gridColor } },
                        y: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    },
                    plugins: { legend: { labels: { color: axisColor } } },
                },
            });

            createChart('categoryTrendChart', {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: categorySeries.map(function (series, index) {
                        const colors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'];
                        return {
                            label: series.label,
                            data: series.data,
                            borderColor: colors[index % colors.length],
                            backgroundColor: 'transparent',
                            pointRadius: 0,
                            tension: 0.3,
                            borderWidth: 2,
                        };
                    }),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: axisColor, maxRotation: 0 }, grid: { color: gridColor } },
                        y: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    },
                    plugins: { legend: { labels: { color: axisColor } } },
                },
            });
            // ── Category × month heatmap canvas ──
            (function () {
                const canvas = document.getElementById('categoryHeatmapCanvas');
                if (!canvas || !categoryHeatmapData.length) { return; }

                const dpr     = window.devicePixelRatio || 1;
                const numCats = categoryHeatmapData.length;
                const PAD_L   = 112;
                const PAD_T   = 28;
                const PAD_R   = 8;
                const PAD_B   = 50;
                const ROW_H   = 38;
                const ROW_GAP = 3;
                const COL_GAP = 3;
                const RADIUS  = 4;

                const containerW = (canvas.parentElement && canvas.parentElement.clientWidth > 0)
                    ? canvas.parentElement.clientWidth : 560;
                const cellW  = Math.max(32, Math.floor((containerW - PAD_L - PAD_R - 11 * COL_GAP) / 12));
                const totalW = PAD_L + 12 * cellW + 11 * COL_GAP + PAD_R;
                const totalH = PAD_T + numCats * (ROW_H + ROW_GAP) - ROW_GAP + PAD_B;

                canvas.width        = Math.round(totalW * dpr);
                canvas.height       = Math.round(totalH * dpr);
                canvas.style.width  = totalW + 'px';
                canvas.style.height = totalH + 'px';

                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);

                function rr(x, y, w, h, r) {
                    r = Math.min(r, w / 2, h / 2);
                    ctx.beginPath();
                    ctx.moveTo(x + r, y);
                    ctx.lineTo(x + w - r, y);
                    ctx.arcTo(x + w, y, x + w, y + r, r);
                    ctx.lineTo(x + w, y + h - r);
                    ctx.arcTo(x + w, y + h, x + w - r, y + h, r);
                    ctx.lineTo(x + r, y + h);
                    ctx.arcTo(x, y + h, x, y + h - r, r);
                    ctx.lineTo(x, y + r);
                    ctx.arcTo(x, y, x + r, y, r);
                    ctx.closePath();
                }

                function valColors(v) {
                    if (v <= 0)     return isDark ? ['#1e293b', '#475569'] : ['#f0f9ff', '#cbd5e1'];
                    if (v <= 500)   return isDark ? ['#172554', '#93c5fd'] : ['#dbeafe', '#1e40af'];
                    if (v <= 1000)  return isDark ? ['#1e3a8a', '#bfdbfe'] : ['#bfdbfe', '#1e3a8a'];
                    if (v <= 2000)  return isDark ? ['#1d4ed8', '#dbeafe'] : ['#93c5fd', '#1e3a8a'];
                    if (v <= 5000)  return isDark ? ['#2563eb', '#eff6ff'] : ['#3b82f6', '#1e3a8a'];
                    if (v <= 10000) return isDark ? ['#3b82f6', '#ffffff'] : ['#1d4ed8', '#ffffff'];
                    return                 isDark ? ['#60a5fa', '#ffffff'] : ['#1e3a8a', '#ffffff'];
                }

                function fmtK(v) {
                    if (v <= 0)       return '';
                    if (v >= 1000000) return (v / 1000000).toFixed(1).replace('.0', '') + 'M';
                    if (v >= 1000)    return (v / 1000).toFixed(1).replace('.0', '') + 'K';
                    return String(Math.round(v));
                }

                const labelCol  = isDark ? '#94a3b8' : '#64748b';
                const catLblCol = isDark ? '#d1d5db' : '#374151';

                // Month headers
                ctx.font      = '10px system-ui,-apple-system,sans-serif';
                ctx.fillStyle = labelCol;
                ctx.textAlign = 'center';
                catMonthLabels.forEach(function (mon, mi) {
                    ctx.fillText(mon, PAD_L + mi * (cellW + COL_GAP) + cellW / 2, 18);
                });

                // Category rows
                categoryHeatmapData.forEach(function (row, ri) {
                    const cy = PAD_T + ri * (ROW_H + ROW_GAP);

                    // Label (right-aligned, truncated)
                    ctx.font      = '11px system-ui,-apple-system,sans-serif';
                    ctx.fillStyle = catLblCol;
                    ctx.textAlign = 'right';
                    let lbl = row.name;
                    const maxW = PAD_L - 12;
                    while (lbl.length > 1 && ctx.measureText(lbl + '…').width > maxW) {
                        lbl = lbl.slice(0, -1);
                    }
                    if (lbl !== row.name) { lbl += '…'; }
                    ctx.fillText(lbl, PAD_L - 8, cy + ROW_H / 2 + 4);

                    // Cells
                    row.months.forEach(function (val, mi) {
                        const cx     = PAD_L + mi * (cellW + COL_GAP);
                        const colors = valColors(val);

                        ctx.fillStyle = colors[0];
                        rr(cx, cy, cellW, ROW_H, RADIUS);
                        ctx.fill();

                        if (val > 0) {
                            ctx.font      = 'bold 10px system-ui,-apple-system,sans-serif';
                            ctx.fillStyle = colors[1];
                            ctx.textAlign = 'center';
                            ctx.fillText(fmtK(val), cx + cellW / 2, cy + ROW_H / 2 + 4);
                        }
                    });
                });

                // Legend
                const legendRanges = [
                    ['0–500₺',   250],
                    ['501–1K₺',  750],
                    ['1K–2K₺',   1500],
                    ['2K–5K₺',   3500],
                    ['5K–10K₺',  7500],
                    ['10K+₺',         15000],
                ];
                const LGY = PAD_T + numCats * (ROW_H + ROW_GAP) - ROW_GAP + 16;
                const BOX = 12;
                let lx    = PAD_L;

                ctx.font      = '10px system-ui,-apple-system,sans-serif';
                ctx.textAlign = 'left';
                legendRanges.forEach(function (item) {
                    const c = valColors(item[1]);
                    ctx.fillStyle = c[0];
                    rr(lx, LGY, BOX, BOX, 2);
                    ctx.fill();
                    ctx.fillStyle = labelCol;
                    ctx.fillText(item[0], lx + BOX + 4, LGY + 10);
                    lx += BOX + 4 + Math.ceil(ctx.measureText(item[0]).width) + 10;
                });
            }());

            // ── Income Category × month heatmap canvas ──
            (function () {
                const canvas = document.getElementById('incomeCategoryHeatmapCanvas');
                if (!canvas || !incomeCategoryHeatmapData.length) { return; }

                const dpr     = window.devicePixelRatio || 1;
                const numCats = incomeCategoryHeatmapData.length;
                const PAD_L   = 112;
                const PAD_T   = 28;
                const PAD_R   = 8;
                const PAD_B   = 50;
                const ROW_H   = 38;
                const ROW_GAP = 3;
                const COL_GAP = 3;
                const RADIUS  = 4;

                const containerW = (canvas.parentElement && canvas.parentElement.clientWidth > 0)
                    ? canvas.parentElement.clientWidth : 560;
                const cellW  = Math.max(32, Math.floor((containerW - PAD_L - PAD_R - 11 * COL_GAP) / 12));
                const totalW = PAD_L + 12 * cellW + 11 * COL_GAP + PAD_R;
                const totalH = PAD_T + numCats * (ROW_H + ROW_GAP) - ROW_GAP + PAD_B;

                canvas.width        = Math.round(totalW * dpr);
                canvas.height       = Math.round(totalH * dpr);
                canvas.style.width  = totalW + 'px';
                canvas.style.height = totalH + 'px';

                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);

                function rr(x, y, w, h, r) {
                    r = Math.min(r, w / 2, h / 2);
                    ctx.beginPath();
                    ctx.moveTo(x + r, y);
                    ctx.lineTo(x + w - r, y);
                    ctx.arcTo(x + w, y, x + w, y + r, r);
                    ctx.lineTo(x + w, y + h - r);
                    ctx.arcTo(x + w, y + h, x + w - r, y + h, r);
                    ctx.lineTo(x + r, y + h);
                    ctx.arcTo(x, y + h, x, y + h - r, r);
                    ctx.lineTo(x, y + r);
                    ctx.arcTo(x, y, x + r, y, r);
                    ctx.closePath();
                }

                function incomeValColors(v) {
                    if (v <= 0)     return isDark ? ['#052e16', '#4ade80'] : ['#f0fdf4', '#bbf7d0'];
                    if (v <= 500)   return isDark ? ['#14532d', '#86efac'] : ['#dcfce7', '#166534'];
                    if (v <= 1000)  return isDark ? ['#166534', '#bbf7d0'] : ['#bbf7d0', '#166534'];
                    if (v <= 2000)  return isDark ? ['#15803d', '#dcfce7'] : ['#86efac', '#166534'];
                    if (v <= 5000)  return isDark ? ['#16a34a', '#f0fdf4'] : ['#4ade80', '#14532d'];
                    if (v <= 10000) return isDark ? ['#22c55e', '#ffffff'] : ['#16a34a', '#ffffff'];
                    return                 isDark ? ['#4ade80', '#ffffff'] : ['#15803d', '#ffffff'];
                }

                function fmtK(v) {
                    if (v <= 0)       return '';
                    if (v >= 1000000) return (v / 1000000).toFixed(1).replace('.0', '') + 'M';
                    if (v >= 1000)    return (v / 1000).toFixed(1).replace('.0', '') + 'K';
                    return String(Math.round(v));
                }

                const labelCol  = isDark ? '#94a3b8' : '#64748b';
                const catLblCol = isDark ? '#d1d5db' : '#374151';

                // Month headers
                ctx.font      = '10px system-ui,-apple-system,sans-serif';
                ctx.fillStyle = labelCol;
                ctx.textAlign = 'center';
                catMonthLabels.forEach(function (mon, mi) {
                    ctx.fillText(mon, PAD_L + mi * (cellW + COL_GAP) + cellW / 2, 18);
                });

                // Category rows
                incomeCategoryHeatmapData.forEach(function (row, ri) {
                    const cy = PAD_T + ri * (ROW_H + ROW_GAP);

                    ctx.font      = '11px system-ui,-apple-system,sans-serif';
                    ctx.fillStyle = catLblCol;
                    ctx.textAlign = 'right';
                    let lbl = row.name;
                    const maxW = PAD_L - 12;
                    while (lbl.length > 1 && ctx.measureText(lbl + '…').width > maxW) {
                        lbl = lbl.slice(0, -1);
                    }
                    if (lbl !== row.name) { lbl += '…'; }
                    ctx.fillText(lbl, PAD_L - 8, cy + ROW_H / 2 + 4);

                    row.months.forEach(function (val, mi) {
                        const cx     = PAD_L + mi * (cellW + COL_GAP);
                        const colors = incomeValColors(val);

                        ctx.fillStyle = colors[0];
                        rr(cx, cy, cellW, ROW_H, RADIUS);
                        ctx.fill();

                        if (val > 0) {
                            ctx.font      = 'bold 10px system-ui,-apple-system,sans-serif';
                            ctx.fillStyle = colors[1];
                            ctx.textAlign = 'center';
                            ctx.fillText(fmtK(val), cx + cellW / 2, cy + ROW_H / 2 + 4);
                        }
                    });
                });

                // Legend
                const legendRanges = [
                    ['0–500₺',   250],
                    ['501–1K₺',  750],
                    ['1K–2K₺',   1500],
                    ['2K–5K₺',   3500],
                    ['5K–10K₺',  7500],
                    ['10K+₺',    15000],
                ];
                const LGY = PAD_T + numCats * (ROW_H + ROW_GAP) - ROW_GAP + 16;
                const BOX = 12;
                let lx    = PAD_L;

                ctx.font      = '10px system-ui,-apple-system,sans-serif';
                ctx.textAlign = 'left';
                legendRanges.forEach(function (item) {
                    const c = incomeValColors(item[1]);
                    ctx.fillStyle = c[0];
                    rr(lx, LGY, BOX, BOX, 2);
                    ctx.fill();
                    ctx.fillStyle = labelCol;
                    ctx.fillText(item[0], lx + BOX + 4, LGY + 10);
                    lx += BOX + 4 + Math.ceil(ctx.measureText(item[0]).width) + 10;
                });
            }());
        });
    </script>
@endsection
