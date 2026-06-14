<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreIncomeRequest;
use App\Http\Requests\Alice\UpdateIncomeRequest;
use App\Http\Resources\Alice\IncomeResource;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Income::with(['source', 'type', 'currency'])
            ->when($request->query('q'), fn ($q, $search) => $q->where('description', 'like', "%{$search}%"))
            ->when($request->query('income_source_id'), fn ($q, $id) => $q->where('income_source_id', $id))
            ->when($request->query('income_type_id'), fn ($q, $id) => $q->where('income_type_id', $id))
            ->when($request->query('currency_id'), fn ($q, $id) => $q->where('currency_id', $id))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applyDateRange($query, $request, 'date');
        $this->applySort($query, $request, ['date', 'amount', 'created_at']);

        return $this->paginate($query, $request, IncomeResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $income = Income::with(['source', 'type', 'currency'])->find($id);

        return $income ? $this->success(new IncomeResource($income)) : $this->notFound();
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        $income = Income::create($request->validated());
        $income->load(['source', 'type', 'currency']);

        return $this->success(new IncomeResource($income), 201);
    }

    public function update(UpdateIncomeRequest $request, int $id): JsonResponse
    {
        $income = Income::find($id);
        if (! $income) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $income);
        $income->update($request->validated());
        $income->load(['source', 'type', 'currency']);

        return $this->success(new IncomeResource($income));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $income = Income::find($id);
        if (! $income) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $income);
        $income->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
