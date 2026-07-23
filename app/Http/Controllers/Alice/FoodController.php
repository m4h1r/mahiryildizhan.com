<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreFoodRequest;
use App\Http\Requests\Alice\UpdateFoodRequest;
use App\Http\Resources\Alice\FoodResource;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Food::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('unit_type'), fn ($q, $type) => $q->where('unit_type', $type))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applySort($query, $request, ['name', 'calories_per_100g', 'created_at']);

        return $this->paginate($query, $request, FoodResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $food = Food::find($id);

        return $food ? $this->success(new FoodResource($food)) : $this->notFound();
    }

    public function store(StoreFoodRequest $request): JsonResponse
    {
        $food = Food::create($request->validated());

        return $this->success(new FoodResource($food), 201);
    }

    public function update(UpdateFoodRequest $request, int $id): JsonResponse
    {
        $food = Food::find($id);
        if (! $food) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $food);
        $food->update($request->validated());

        return $this->success(new FoodResource($food));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $food = Food::find($id);
        if (! $food) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $food);
        $food->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
