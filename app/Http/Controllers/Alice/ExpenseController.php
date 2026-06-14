<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreExpenseRequest;
use App\Http\Requests\Alice\UpdateExpenseRequest;
use App\Http\Resources\Alice\ExpenseResource;
use App\Models\Expense;
use App\Services\AliceLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends AliceController
{
    public function __construct(private AliceLookupService $lookup) {}

    public function index(Request $request): JsonResponse
    {
        $query = Expense::with(['expenseType', 'currency', 'stakeholder'])
            ->when($request->query('q'), fn ($q, $search) => $q->where('description', 'like', "%{$search}%"))
            ->when($request->query('expense_type_id'), fn ($q, $id) => $q->where('expense_type_id', $id))
            ->when($request->query('currency_id'), fn ($q, $id) => $q->where('currency_id', $id))
            ->when($request->query('stakeholder_id'), fn ($q, $id) => $q->where('stakeholder_id', $id))
            ->when($request->boolean('company_expense'), fn ($q) => $q->where('company_expense', true))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applyDateRange($query, $request, 'date');
        $this->applySort($query, $request, ['date', 'total', 'created_at']);

        return $this->paginate($query, $request, ExpenseResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $expense = Expense::with(['expenseType', 'currency', 'stakeholder'])->find($id);

        return $expense ? $this->success(new ExpenseResource($expense)) : $this->notFound();
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Resolve stakeholder by name if string
        if (isset($data['stakeholder']) && ! is_numeric($data['stakeholder'])) {
            $resolved = $this->lookup->resolveStakeholder($data['stakeholder']);
            $data['stakeholder_id'] = $resolved['id'];
            unset($data['stakeholder']);
        }

        $expense = Expense::create($data);
        $expense->load(['expenseType', 'currency', 'stakeholder']);

        return $this->success(new ExpenseResource($expense), 201);
    }

    public function update(UpdateExpenseRequest $request, int $id): JsonResponse
    {
        $expense = Expense::find($id);
        if (! $expense) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $expense);
        $data = $request->validated();

        if (isset($data['stakeholder']) && ! is_numeric($data['stakeholder'])) {
            $resolved = $this->lookup->resolveStakeholder($data['stakeholder']);
            $data['stakeholder_id'] = $resolved['id'];
            unset($data['stakeholder']);
        }

        $expense->update($data);
        $expense->load(['expenseType', 'currency', 'stakeholder']);

        return $this->success(new ExpenseResource($expense));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $expense = Expense::find($id);
        if (! $expense) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $expense);
        $expense->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
