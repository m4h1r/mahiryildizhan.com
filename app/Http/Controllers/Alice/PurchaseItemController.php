<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StorePurchaseItemRequest;
use App\Http\Requests\Alice\UpdatePurchaseItemRequest;
use App\Http\Resources\Alice\PurchaseItemResource;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseItemController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseItem::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($request->has('is_completed'), fn ($q) => $q->where('is_completed', $request->boolean('is_completed')))
            ->when($request->has('is_grocery'), fn ($q) => $q->where('is_grocery', $request->boolean('is_grocery')))
            ->when($request->has('is_bucketlist'), fn ($q) => $q->where('is_bucketlist', $request->boolean('is_bucketlist')))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applySort($query, $request, ['title', 'cost_try', 'created_at']);

        return $this->paginate($query, $request, PurchaseItemResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $item = PurchaseItem::find($id);

        return $item ? $this->success(new PurchaseItemResource($item)) : $this->notFound();
    }

    public function store(StorePurchaseItemRequest $request): JsonResponse
    {
        $item = PurchaseItem::create($request->validated());

        return $this->success(new PurchaseItemResource($item), 201);
    }

    public function update(UpdatePurchaseItemRequest $request, int $id): JsonResponse
    {
        $item = PurchaseItem::find($id);
        if (! $item) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $item);
        $data = $request->validated();

        if (isset($data['is_completed']) && $data['is_completed'] && ! $item->is_completed) {
            $data['completed_at'] = now();
        }

        $item->update($data);

        return $this->success(new PurchaseItemResource($item));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = PurchaseItem::find($id);
        if (! $item) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $item);
        $item->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
