<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) ($request->integer('year') ?: now()->year);
        $dailyRange = $request->string('daily_range')->toString();
        if (! in_array($dailyRange, ['week', 'month', 'ytd', 'year'], true)) {
            $dailyRange = 'month';
        }

        $tryCurrencyId = Currency::query()->where('code', 'TRY')->value('id');

        $incomeQuery = Income::query()->whereYear('date', $year);
        $expenseQuery = Expense::query()->whereYear('date', $year)->where('paid_by_others', false);

        if ($tryCurrencyId) {
            $incomeQuery->where('currency_id', $tryCurrencyId);
            $expenseQuery->where('currency_id', $tryCurrencyId);
        }

        $annualIncome = (float) $incomeQuery->sum('amount');
        $annualExpense = (float) $expenseQuery->sum('total');
        $netBalance = $annualIncome - $annualExpense;

        $monthly = [
            'labels' => [],
            'company' => [],
            'personal' => [],
            'tax' => [],
            'income' => [],
        ];

        for ($month = 1; $month <= 12; $month++) {
            $monthly['labels'][] = Carbon::create($year, $month, 1)->format('M');

            $baseExpenseMonth = Expense::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->where('paid_by_others', false);

            if ($tryCurrencyId) {
                $baseExpenseMonth->where('currency_id', $tryCurrencyId);
            }

            $monthly['company'][] = (float) (clone $baseExpenseMonth)->where('company_expense', true)->sum('total');
            $monthly['personal'][] = (float) (clone $baseExpenseMonth)->where('company_expense', false)->sum('total');
            $monthly['tax'][] = (float) (clone $baseExpenseMonth)->sum('tax');

            $incomeMonth = Income::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $month);

            if ($tryCurrencyId) {
                $incomeMonth->where('currency_id', $tryCurrencyId);
            }

            $monthly['income'][] = (float) $incomeMonth->sum('amount');
        }

        $monthlyExpenseTotals = [];
        $monthlyNet = [];
        $cumulativeNet = [];
        $runningNet = 0.0;

        foreach ($monthly['labels'] as $index => $label) {
            $expenseTotal = (float) $monthly['company'][$index] + (float) $monthly['personal'][$index];
            $net = (float) $monthly['income'][$index] - $expenseTotal;

            $monthlyExpenseTotals[] = $expenseTotal;
            $monthlyNet[] = $net;

            $runningNet += $net;
            $cumulativeNet[] = $runningNet;
        }

        $topExpenseMonthIndex = ! empty($monthlyExpenseTotals)
            ? array_keys($monthlyExpenseTotals, max($monthlyExpenseTotals), true)[0]
            : 0;
        $topIncomeMonthIndex = ! empty($monthly['income'])
            ? array_keys($monthly['income'], max($monthly['income']), true)[0]
            : 0;

        $avgMonthlyExpense = count($monthlyExpenseTotals) > 0
            ? array_sum($monthlyExpenseTotals) / count($monthlyExpenseTotals)
            : 0.0;
        $avgMonthlyIncome = count($monthly['income']) > 0
            ? array_sum($monthly['income']) / count($monthly['income'])
            : 0.0;
        $savingsRate = $annualIncome > 0
            ? (($annualIncome - $annualExpense) / $annualIncome) * 100
            : 0.0;

        $daysElapsed = $year === now()->year
            ? max(1, now()->dayOfYear)
            : Carbon::create($year, 12, 31)->dayOfYear;

        $expenseTypeRows = ExpenseType::query()
            ->orderBy('name')
            ->get()
            ->map(function (ExpenseType $expenseType) use ($year, $tryCurrencyId, $daysElapsed): array {
                $query = Expense::query()
                    ->whereYear('date', $year)
                    ->where('paid_by_others', false)
                    ->where('expense_type_id', $expenseType->id);

                if ($tryCurrencyId) {
                    $query->where('currency_id', $tryCurrencyId);
                }

                $total = (float) $query->sum('total');

                return [
                    'name' => $expenseType->name,
                    'total' => $total,
                    'daily_avg' => $total / $daysElapsed,
                    'monthly_avg' => $total / 12,
                ];
            })
            ->filter(fn (array $row) => $row['total'] > 0)
            ->values();

        $periodStart = Carbon::create($year, 1, 1)->startOfDay();
        $periodEnd = Carbon::create($year, 12, 31)->endOfDay();
        $today = now()->endOfDay();

        if ($year === (int) now()->year && $today->lt($periodEnd)) {
            $periodEnd = $today;
        }

        if ($dailyRange === 'week') {
            $periodStart = (clone $periodEnd)->subDays(6)->startOfDay()->max(Carbon::create($year, 1, 1)->startOfDay());
        } elseif ($dailyRange === 'month') {
            $periodStart = (clone $periodEnd)->subDays(29)->startOfDay()->max(Carbon::create($year, 1, 1)->startOfDay());
        } elseif ($dailyRange === 'ytd') {
            $periodStart = Carbon::create($year, 1, 1)->startOfDay();
        }

        $dailyBase = Expense::query()
            ->where('paid_by_others', false)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        if ($tryCurrencyId) {
            $dailyBase->where('currency_id', $tryCurrencyId);
        }

        $dailyTotals = (clone $dailyBase)
            ->selectRaw('DATE(date) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $dailyExpenseLabels = [];
        $dailyExpenseTotals = [];
        $cursor = $periodStart->copy();

        while ($cursor->lte($periodEnd)) {
            $key = $cursor->toDateString();
            $dailyExpenseLabels[] = $cursor->format('d M');
            $dailyExpenseTotals[] = (float) ($dailyTotals[$key] ?? 0);
            $cursor->addDay();
        }

        $topCategoryIds = (clone $dailyBase)
            ->whereNotNull('expense_type_id')
            ->select('expense_type_id', DB::raw('SUM(total) as total_sum'))
            ->groupBy('expense_type_id')
            ->orderByDesc('total_sum')
            ->limit(10)
            ->pluck('expense_type_id')
            ->all();

        $categoryNames = ExpenseType::query()
            ->whereIn('id', $topCategoryIds)
            ->pluck('name', 'id');

        $categoryDailyRows = (clone $dailyBase)
            ->whereIn('expense_type_id', $topCategoryIds)
            ->selectRaw('expense_type_id, DATE(date) as day, SUM(total) as total')
            ->groupBy('expense_type_id', 'day')
            ->orderBy('day')
            ->get();

        $categoryMatrix = [];
        foreach ($categoryDailyRows as $row) {
            $categoryMatrix[(int) $row->expense_type_id][$row->day] = (float) $row->total;
        }

        $dailyCategoryData = [];
        foreach ($topCategoryIds as $categoryId) {
            $series = [];
            $cursor = $periodStart->copy();

            while ($cursor->lte($periodEnd)) {
                $series[] = (float) ($categoryMatrix[(int) $categoryId][$cursor->toDateString()] ?? 0);
                $cursor->addDay();
            }

            $dailyCategoryData[] = [
                'label' => (string) ($categoryNames[$categoryId] ?? 'Other'),
                'data' => $series,
            ];
        }

        return view('admin.reports', [
            'year' => $year,
            'dailyRange' => $dailyRange,
            'annualIncome' => $annualIncome,
            'annualExpense' => $annualExpense,
            'netBalance' => $netBalance,
            'monthly' => $monthly,
            'monthlyExpenseTotals' => $monthlyExpenseTotals,
            'monthlyNet' => $monthlyNet,
            'cumulativeNet' => $cumulativeNet,
            'expenseTypeRows' => $expenseTypeRows,
            'expenseTypeChart' => [
                'labels' => $expenseTypeRows->pluck('name')->all(),
                'values' => $expenseTypeRows->pluck('total')->all(),
            ],
            'dailyExpenseLabels' => $dailyExpenseLabels,
            'dailyExpenseTotals' => $dailyExpenseTotals,
            'dailyCategoryData' => $dailyCategoryData,
            'insights' => [
                'avg_monthly_income' => $avgMonthlyIncome,
                'avg_monthly_expense' => $avgMonthlyExpense,
                'savings_rate' => $savingsRate,
                'top_expense_month' => [
                    'label' => $monthly['labels'][$topExpenseMonthIndex] ?? '-',
                    'value' => $monthlyExpenseTotals[$topExpenseMonthIndex] ?? 0,
                ],
                'top_income_month' => [
                    'label' => $monthly['labels'][$topIncomeMonthIndex] ?? '-',
                    'value' => $monthly['income'][$topIncomeMonthIndex] ?? 0,
                ],
            ],
        ]);
    }
}