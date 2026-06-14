<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreInteractionRequest;
use App\Http\Requests\Alice\UpdateInteractionRequest;
use App\Http\Resources\Alice\InteractionResource;
use App\Models\Interaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InteractionController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Interaction::with(['person', 'type'])
            ->when($request->query('q'), fn ($q, $search) => $q->where('notes', 'like', "%{$search}%"))
            ->when($request->query('person_id'), fn ($q, $id) => $q->where('person_id', $id))
            ->when($request->query('interaction_type_id'), fn ($q, $id) => $q->where('interaction_type_id', $id));

        $this->applyDateRange($query, $request, 'date');
        $this->applySort($query, $request, ['date', 'created_at']);

        return $this->paginate($query, $request, InteractionResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $interaction = Interaction::with(['person', 'type'])->find($id);

        return $interaction ? $this->success(new InteractionResource($interaction)) : $this->notFound();
    }

    public function store(StoreInteractionRequest $request): JsonResponse
    {
        $interaction = Interaction::create($request->validated());
        $interaction->load(['person', 'type']);

        return $this->success(new InteractionResource($interaction), 201);
    }

    public function update(UpdateInteractionRequest $request, int $id): JsonResponse
    {
        $interaction = Interaction::find($id);
        if (! $interaction) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $interaction);
        $interaction->update($request->validated());
        $interaction->load(['person', 'type']);

        return $this->success(new InteractionResource($interaction));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $interaction = Interaction::find($id);
        if (! $interaction) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $interaction);
        $interaction->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
