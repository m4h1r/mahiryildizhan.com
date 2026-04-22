<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Stakeholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::query()
            ->with(['stakeholder', 'expenseType', 'currency'])
            ->latest('date')
            ->latest('id');

        if ($dateFrom = $request->date('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->date('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($year = $request->integer('year')) {
            $query->whereYear('date', $year);
        }

        if ($month = $request->integer('month')) {
            $query->whereMonth('date', $month);
        }

        if ($typeId = $request->integer('expense_type_id')) {
            $query->where('expense_type_id', $typeId);
        }

        if ($currencyId = $request->integer('currency_id')) {
            $query->where('currency_id', $currencyId);
        }

        if ($stakeholderId = $request->integer('stakeholder_id')) {
            $query->where('stakeholder_id', $stakeholderId);
        }

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('stakeholder', function ($b) use ($search): void {
                        $b->where(function ($q2) use ($search): void {
                            $q2->where('title', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                    })
                    ->orWhereHas('expenseType', function ($b) use ($search): void {
                        $b->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($stakeholderQuery = trim((string) $request->string('stakeholder_query'))) {
            $query->whereHas('stakeholder', function ($builder) use ($stakeholderQuery): void {
                $builder->where(function ($q) use ($stakeholderQuery): void {
                    $q->where('vkn_tckn', 'like', "%{$stakeholderQuery}%")
                        ->orWhere('title', 'like', "%{$stakeholderQuery}%")
                        ->orWhere('name', 'like', "%{$stakeholderQuery}%")
                        ->orWhere('surname', 'like', "%{$stakeholderQuery}%");
                });
            });
        }

        if ($request->has('company_expense') && $request->input('company_expense') !== '') {
            $query->where('company_expense', (bool) $request->boolean('company_expense'));
        }

        if ($request->has('paid_by_others') && $request->input('paid_by_others') !== '') {
            $query->where('paid_by_others', (bool) $request->boolean('paid_by_others'));
        }

        $expenses = $query->paginate(20)->withQueryString();

        return view('admin.expenses.index', [
            'title' => 'Expenses',
            'heading' => 'Expenses',
            'expenses' => $expenses,
            'expenseTypes' => ExpenseType::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'stakeholders' => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'filters' => $request->only([
                'search', 'date_from', 'date_to', 'year', 'month', 'expense_type_id', 'currency_id', 'stakeholder_id', 'stakeholder_query', 'company_expense', 'paid_by_others',
            ]),
        ]);
    }

    public function create(): View
    {
        return view('admin.expenses.create', [
            'title' => 'Yeni Gider',
            'heading' => 'Yeni Gider',
            'expenseTypes' => ExpenseType::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'stakeholders' => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'defaultCurrencyId' => Currency::where('code', 'TRY')->value('id'),
            'defaultExpenseTypeId' => ExpenseType::where('name', 'Market')->value('id'),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        Expense::query()->create($this->buildPayload($request->validated()));

        return to_route('admin.expenses.index')->with('success', 'Expense created.');
    }

    public function edit(Expense $expense): View
    {
        return view('admin.expenses.edit', [
            'title' => 'Gider Düzenle',
            'heading' => 'Gider Düzenle',
            'expense' => $expense,
            'expenseTypes' => ExpenseType::query()->orderBy('name')->get(),
            'currencies' => Currency::query()->orderBy('code')->get(),
            'stakeholders' => Stakeholder::query()->orderBy('title')->orderBy('name')->get(),
            'defaultCurrencyId' => null,
            'defaultExpenseTypeId' => null,
        ]);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->buildPayload($request->validated()));

        return to_route('admin.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return to_route('admin.expenses.index')->with('success', 'Expense deleted.');
    }

    public function duplicate(Expense $expense): RedirectResponse
    {
        $copy = $expense->replicate();
        $copy->date = now()->toDateString();
        $copy->save();

        return to_route('admin.expenses.edit', $copy)->with('success', 'Expense duplicated.');
    }

    private function buildPayload(array $payload): array
    {
        $price = (float) $payload['price'];
        $quantity = (float) $payload['quantity'];
        $tax = (float) ($payload['tax'] ?? 0);

        $payload['total'] = $price * $quantity;

        return $payload;
    }
}
