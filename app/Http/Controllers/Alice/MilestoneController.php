<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreMilestoneRequest;
use App\Http\Requests\Alice\UpdateMilestoneRequest;
use App\Http\Resources\Alice\MilestoneResource;
use App\Models\Milestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Milestone::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($request->query('milestoneable_type'), fn ($q, $type) => $q->where('milestoneable_type', $type))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applySort($query, $request, ['title', 'achieved_at', 'created_at']);

        return $this->paginate($query, $request, MilestoneResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $milestone = Milestone::find($id);

        return $milestone ? $this->success(new MilestoneResource($milestone)) : $this->notFound();
    }

    public function store(StoreMilestoneRequest $request): JsonResponse
    {
        $milestone = Milestone::create($request->validated());

        return $this->success(new MilestoneResource($milestone), 201);
    }

    public function update(UpdateMilestoneRequest $request, int $id): JsonResponse
    {
        $milestone = Milestone::find($id);
        if (! $milestone) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $milestone);
        $milestone->update($request->validated());

        return $this->success(new MilestoneResource($milestone));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $milestone = Milestone::find($id);
        if (! $milestone) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $milestone);
        $milestone->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
