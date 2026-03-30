@extends('admin.layout', ['title' => 'Reports', 'heading' => 'Reports'])

@section('content')
    @php
        $availableYears = range(now()->year, max(now()->year - 10, 2015));
        if (! in_array($year, $availableYears, true)) {
            $availableYears[] = $year;
        }
        rsort($availableYears);

        $topRows = collect($expenseTypeRows)->sortByDesc('total')->take(5)->values();
        $netPositive = $netBalance >= 0;
    @endphp

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 px-6 py-6 text-white shadow-xl md:px-8 md:py-8">
            <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-cyan-300/15 blur-2xl"></div>
            <div class="absolute -bottom-16 left-24 h-48 w-48 rounded-full bg-indigo-300/15 blur-2xl"></div>

            <div class="relative space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200/90">Financial Intelligence</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">Reports Command Center · {{ $year }}</h2>
                    <p class="mt-2 max-w-2xl text-sm text-cyan-100/80">Monthly performance, spend composition, trend pressure, and category-level signals in one board.</p>
                </div>

                <form method="GET" class="grid gap-2 rounded-2xl border border-white/20 bg-white/10 p-3 backdrop-blur sm:grid-cols-2 lg:grid-cols-3 max-w-fit">
                    <label class="text-xs font-medium text-cyan-100">
                        <span class="mb-1 block">Year</span>
                        <select name="year" class="form-input-admin !border-white/20 !bg-white/10 !text-white">
                            @foreach ($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" @selected($availableYear === $year)>{{ $availableYear }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-medium text-cyan-100">
                        <span class="mb-1 block">Daily Range</span>
                        <select name="daily_range" class="form-input-admin !border-white/20 !bg-white/10 !text-white">
                            <option value="week" @selected($dailyRange === 'week')>Last 7 days</option>
                            <option value="month" @selected($dailyRange === 'month')>Last 30 days</option>
                            <option value="ytd" @selected($dailyRange === 'ytd')>Year to date</option>
                            <option value="year" @selected($dailyRange === 'year')>Full year</option>
                        </select>
                    </label>
                    <button type="submit" class="admin-btn admin-btn-primary sm:col-span-2 lg:col-span-1">Apply</button>
                </form>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="card-admin">
                <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Financial Summary</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="border-l-4 border-emerald-500 pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Income</p>
                        <p class="mt-1 text-xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($annualIncome, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Avg: {{ number_format($insights['avg_monthly_income'], 2) }}</p>
                    </div>
                    <div class="border-l-4 border-rose-500 pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Expense</p>
                        <p class="mt-1 text-xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($annualExpense, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Avg: {{ number_format($insights['avg_monthly_expense'], 2) }}</p>
                    </div>
                    <div class="border-l-4 {{ $netPositive ? 'border-cyan-500' : 'border-amber-500' }} pl-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Net</p>
                        <p class="mt-1 text-xl font-semibold {{ $netPositive ? 'text-cyan-700 dark:text-cyan-300' : 'text-amber-700 dark:text-amber-300' }}">{{ number_format($netBalance, 2) }} TRY</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Save: {{ number_format($insights['savings_rate'], 1) }}%</p>
                    </div>
                </div>
            </article>
            <article class="card-admin border-l-4 border-violet-500">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Peak Spend Month</p>
                <p class="mt-1 text-2xl font-semibold text-violet-700 dark:text-violet-300">{{ $insights['top_expense_month']['label'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($insights['top_expense_month']['value'], 2) }} TRY</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <article class="card-admin xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">Income vs Expense vs Net</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monthly performance timeline</p>
                </div>
                <div class="h-[22rem]">
                    <canvas id="monthlyPerformanceChart"></canvas>
                </div>
            </article>

            <article class="card-admin">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">Expense Distribution</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">By category</p>
                </div>
                <div class="h-[22rem]">
                    <canvas id="expenseShareChart"></canvas>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <article class="card-admin xl:col-span-3">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">Daily Expense Pressure</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Rolling daily totals</p>
                </div>
                <div class="h-[21rem]">
                    <canvas id="dailyExpenseChart"></canvas>
                </div>
            </article>

            <article class="card-admin xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">Cumulative Net Curve</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Running result across months</p>
                </div>
                <div class="h-[21rem]">
                    <canvas id="cumulativeNetChart"></canvas>
                </div>
            </article>
        </section>

        <section class="card-admin">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold">Category Trend Signals</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Top categories over selected period</p>
            </div>
            <div class="h-[24rem]">
                <canvas id="categoryTrendChart"></canvas>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @forelse ($topRows as $row)
                <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/50">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</p>
                    <p class="mt-1 text-lg font-semibold text-rose-700 dark:text-rose-300">{{ number_format($row['total'], 2) }} TRY</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daily: {{ number_format($row['daily_avg'], 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monthly: {{ number_format($row['monthly_avg'], 2) }}</p>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No TRY expense data for this period.</p>
            @endforelse
        </section>

        <section class="card-admin overflow-x-auto">
            <h3 class="px-4 pt-4 text-sm font-semibold">Expense Type Analysis Table</h3>
            <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Type</th>
                        <th class="px-3 py-2 text-left font-medium">Total</th>
                        <th class="px-3 py-2 text-left font-medium">Daily Avg</th>
                        <th class="px-3 py-2 text-left font-medium">Monthly Avg</th>
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
                        <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No TRY expense data for this year.</td></tr>
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

            const monthly = @json($monthly);
            const monthlyExpenseTotals = @json($monthlyExpenseTotals);
            const monthlyNet = @json($monthlyNet);
            const cumulativeNet = @json($cumulativeNet);
            const expenseTypeChart = @json($expenseTypeChart);
            const dailyLabels = @json($dailyExpenseLabels);
            const dailyTotals = @json($dailyExpenseTotals);
            const categorySeries = @json($dailyCategoryData);
            const isDark = document.documentElement.classList.contains('dark');
            const axisColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(100,116,139,0.16)';

            const createChart = function (id, config) {
                const element = document.getElementById(id);
                if (!element) {
                    return null;
                }

                const context = element.getContext('2d');
                if (!context) {
                    return null;
                }

                return new window.Chart(context, config);
            };

            createChart('monthlyPerformanceChart', {
                type: 'bar',
                data: {
                    labels: monthly.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Income',
                            data: monthly.income,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderRadius: 6,
                        },
                        {
                            type: 'bar',
                            label: 'Expense',
                            data: monthlyExpenseTotals,
                            backgroundColor: 'rgba(244, 63, 94, 0.7)',
                            borderRadius: 6,
                        },
                        {
                            type: 'line',
                            label: 'Net',
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
                    plugins: {
                        legend: { labels: { color: axisColor } },
                    },
                },
            });

            createChart('expenseShareChart', {
                type: 'doughnut',
                data: {
                    labels: expenseTypeChart.labels,
                    datasets: [{
                        data: expenseTypeChart.values,
                        backgroundColor: [
                            '#2563eb', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899', '#06b6d4', '#84cc16',
                        ],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: axisColor } },
                    },
                },
            });

            createChart('dailyExpenseChart', {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Daily Expense',
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
                    plugins: {
                        legend: { labels: { color: axisColor } },
                    },
                },
            });

            createChart('cumulativeNetChart', {
                type: 'line',
                data: {
                    labels: monthly.labels,
                    datasets: [{
                        label: 'Cumulative Net',
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
                    plugins: {
                        legend: { labels: { color: axisColor } },
                    },
                },
            });

            const categoryDatasets = categorySeries.map(function (series, index) {
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
            });

            createChart('categoryTrendChart', {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: categoryDatasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: axisColor, maxRotation: 0 }, grid: { color: gridColor } },
                        y: { ticks: { color: axisColor }, grid: { color: gridColor } },
                    },
                    plugins: {
                        legend: {
                            labels: { color: axisColor },
                        },
                    },
                },
            });
        });
    </script>
@endsection
