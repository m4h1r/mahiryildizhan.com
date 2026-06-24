<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeRequest;
use App\Models\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\Person;
use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function index(Request $request): View
    {
        $sortable  = ['date', 'amount', 'type'];
        $sort      = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $year         = $request->has('year') ? ($request->integer('year') ?: null) : now()->year;
        $month        = $request->integer('month') ?: null;
        $typeId       = $request->integer('income_type_id') ?: null;
        $currencyId   = $request->integer('currency_id') ?: null;
        $userId       = $request->integer('user_id') ?: null;
        $sourceableKey = $request->string('sourceable')->toString() ?: null;
        [$sourceableType, $sourceableId] = $this->parseSourceableKey($sourceableKey);

        $applyFilters = function ($q) use ($year, $month, $typeId, $currencyId, $userId, $sourceableType, $sourceableId) {
            if ($year)       { $q->whereYear('incomes.date', $year); }
            if ($month)      { $q->whereMonth('incomes.date', $month); }
            if ($typeId)     { $q->where('incomes.income_type_id', $typeId); }
            if ($currencyId) { $q->where('incomes.currency_id', $currencyId); }
            if ($userId)     { $q->where('incomes.user_id', $userId); }
            if ($sourceableType && $sourceableId) {
                $q->where('incomes.sourceable_type', $sourceableType)->where('incomes.sourceable_id', $sourceableId);
            }

            return $q;
        };

        $query = Income::query()
            ->with(['source', 'sourceable', 'type', 'currency', 'user'])
            ->select('incomes.*');

        if ($sort === 'type') {
            $query->leftJoin('income_types', 'income_types.id', '=', 'incomes.income_type_id')
                  ->orderBy('income_types.name', $direction);
        } else {
            $query->orderBy('incomes.'.$sort, $direction);
        }

        $query->orderBy('incomes.id', 'desc');

        $applyFilters($query);

        $incomes = $query->paginate(20)->withQueryString();

        $chartQuery = Income::query()
            ->leftJoin('income_types', 'income_types.id', '=', 'incomes.income_type_id')
            ->select('income_types.name as type_name', DB::raw('SUM(incomes.amount) as total'))
            ->groupBy('income_types.name')
            ->orderByDesc('total');

        $applyFilters($chartQuery);

        $chartRows = $chartQuery->get();

        $incomeTypeChart = [
            'labels' => $chartRows->pluck('type_name')->map(fn ($n) => $n ?? 'Diğer')->all(),
            'values' => $chartRows->pluck('total')->map(fn ($v) => (float) $v)->all(),
        ];

        $hourlyRateQuery = Income::query()
            ->join('currencies', 'currencies.id', '=', 'incomes.currency_id')
            ->whereNotNull('incomes.hours')
            ->where('incomes.hours', '>', 0)
            ->select('currencies.code as currency_code', DB::raw('AVG(incomes.amount / incomes.hours) as avg_rate'))
            ->groupBy('currencies.code')
            ->orderByDesc('avg_rate');

        $applyFilters($hourlyRateQuery);

        $averageHourlyRates = $hourlyRateQuery->get();

        $availableYears = Income::query()
            ->whereNotNull('date')
            ->pluck('date')
            ->map(fn ($date) => (int) \Illuminate\Support\Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('admin.incomes.index', [
            'title'          => 'Gelirler',
            'heading'        => 'Gelirler',
            'incomes'        => $incomes,
            'people'         => Person::query()->orderBy('surname')->orderBy('name')->get(),
            'stakeholders'   => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'types'          => IncomeType::query()->orderBy('name')->get(),
            'currencies'     => Currency::query()->orderBy('code')->get(),
            'users'          => User::query()->orderBy('name')->get(),
            'filters'        => array_merge($request->only(['month', 'sourceable', 'income_type_id', 'currency_id', 'user_id']), ['year' => $year]),
            'sort'           => $sort,
            'direction'      => $direction,
            'incomeTypeChart' => $incomeTypeChart,
            'averageHourlyRates' => $averageHourlyRates,
            'availableYears' => $availableYears,
        ]);
    }

    public function create(): View
    {
        return view('admin.incomes.create', [
            'title' => 'New Income',
            'heading' => 'New Income',
            'people' => Person::query()->orderBy('surname')->orderBy('name')->get(),
            'stakeholders' => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'types' => IncomeType::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload = $this->applySourceable($payload, $request->string('sourceable')->toString());

        if (! isset($payload['user_id']) || $payload['user_id'] === null) {
            $payload['user_id'] = $request->user()?->id;
        }

        Income::query()->create($payload);

        return to_route('admin.incomes.index')->with('success', 'Income created.');
    }

    public function edit(Income $income): View
    {
        return view('admin.incomes.edit', [
            'title' => 'Edit Income',
            'heading' => 'Edit Income',
            'income' => $income,
            'people' => Person::query()->orderBy('surname')->orderBy('name')->get(),
            'stakeholders' => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'types' => IncomeType::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'users' => User::query()->orderBy('name')->get(),
            'currentSourceableKey' => $this->sourceableKey($income),
        ]);
    }

    public function update(StoreIncomeRequest $request, Income $income): RedirectResponse
    {
        $payload = $request->validated();
        $payload = $this->applySourceable($payload, $request->string('sourceable')->toString());

        $income->update($payload);

        return to_route('admin.incomes.index')->with('success', 'Income updated.');
    }

    public function destroy(Income $income): RedirectResponse
    {
        $income->delete();

        return to_route('admin.incomes.index')->with('success', 'Income deleted.');
    }

    public function duplicate(Income $income): RedirectResponse
    {
        $copy = $income->replicate();
        $copy->date = now()->toDateString();
        $copy->save();

        return to_route('admin.incomes.edit', $copy)->with('success', 'Income duplicated.');
    }

    /**
     * Parse a "person:<id>" / "stakeholder:<id>" select value into [morph class, id].
     */
    private function parseSourceableKey(?string $key): array
    {
        if (! $key) {
            return [null, null];
        }

        [$type, $id] = array_pad(explode(':', $key, 2), 2, null);

        $model = match ($type) {
            'person' => Person::class,
            'stakeholder' => Stakeholder::class,
            default => null,
        };

        if ($model === null || ! is_numeric($id)) {
            return [null, null];
        }

        return [$model, (int) $id];
    }

    private function applySourceable(array $payload, ?string $key): array
    {
        [$type, $id] = $this->parseSourceableKey($key);

        $payload['sourceable_type'] = $type;
        $payload['sourceable_id'] = $id;

        return $payload;
    }

    private function sourceableKey(Income $income): string
    {
        if (! $income->sourceable_type || ! $income->sourceable_id) {
            return '';
        }

        $prefix = match ($income->sourceable_type) {
            Person::class => 'person',
            Stakeholder::class => 'stakeholder',
            default => null,
        };

        return $prefix ? "{$prefix}:{$income->sourceable_id}" : '';
    }
}
