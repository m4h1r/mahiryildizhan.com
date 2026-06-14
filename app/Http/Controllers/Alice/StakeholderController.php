<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreStakeholderRequest;
use App\Http\Requests\Alice\UpdateStakeholderRequest;
use App\Http\Resources\Alice\StakeholderResource;
use App\Models\Stakeholder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StakeholderController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Stakeholder::query()
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($sub) => $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('vkn_tckn', 'like', "%{$search}%"));
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('company_type'), fn ($q, $type) => $q->where('company_type', $type));

        $this->applySort($query, $request, ['title', 'name', 'created_at']);

        return $this->paginate($query, $request, StakeholderResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $stakeholder = Stakeholder::find($id);

        return $stakeholder ? $this->success(new StakeholderResource($stakeholder)) : $this->notFound();
    }

    public function store(StoreStakeholderRequest $request): JsonResponse
    {
        if ($request->filled('vkn_tckn')) {
            $exists = Stakeholder::where('vkn_tckn', $request->vkn_tckn)->exists();
            if ($exists) {
                return $this->conflict('Bu VKN/TCKN ile kayıtlı bir paydaş zaten mevcut');
            }
        }

        $stakeholder = Stakeholder::create($request->validated());

        return $this->success(new StakeholderResource($stakeholder), 201);
    }

    public function update(UpdateStakeholderRequest $request, int $id): JsonResponse
    {
        $stakeholder = Stakeholder::find($id);
        if (! $stakeholder) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $stakeholder);
        $stakeholder->update($request->validated());

        return $this->success(new StakeholderResource($stakeholder));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $stakeholder = Stakeholder::find($id);
        if (! $stakeholder) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $stakeholder);
        $stakeholder->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
