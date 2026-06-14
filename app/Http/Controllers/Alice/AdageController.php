<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreAdageRequest;
use App\Http\Requests\Alice\UpdateAdageRequest;
use App\Http\Resources\Alice\AdageResource;
use App\Models\Adage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdageController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Adage::with(['language'])
            ->when($request->query('q'), fn ($q, $search) => $q->where('adage', 'like', "%{$search}%")
                ->orWhere('owner', 'like', "%{$search}%")
                ->orWhere('keywords', 'like', "%{$search}%"))
            ->when($request->query('language_id'), fn ($q, $id) => $q->where('language_id', $id))
            ->when($request->query('owner'), fn ($q, $owner) => $q->where('owner', 'like', "%{$owner}%"));

        $this->applySort($query, $request, ['owner', 'created_at']);

        return $this->paginate($query, $request, AdageResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $adage = Adage::with(['language'])->find($id);

        return $adage ? $this->success(new AdageResource($adage)) : $this->notFound();
    }

    public function store(StoreAdageRequest $request): JsonResponse
    {
        $adage = Adage::create($request->validated());
        $adage->load(['language']);

        return $this->success(new AdageResource($adage), 201);
    }

    public function update(UpdateAdageRequest $request, int $id): JsonResponse
    {
        $adage = Adage::find($id);
        if (! $adage) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $adage);
        $adage->update($request->validated());
        $adage->load(['language']);

        return $this->success(new AdageResource($adage));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $adage = Adage::find($id);
        if (! $adage) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $adage);
        $adage->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
