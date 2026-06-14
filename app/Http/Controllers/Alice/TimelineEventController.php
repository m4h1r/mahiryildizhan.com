<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreTimelineEventRequest;
use App\Http\Requests\Alice\UpdateTimelineEventRequest;
use App\Http\Resources\Alice\TimelineEventResource;
use App\Models\TimelineEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineEventController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = TimelineEvent::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->when($request->query('event_type'), fn ($q, $type) => $q->where('event_type', $type))
            ->when($request->has('is_public'), fn ($q) => $q->where('is_public', $request->boolean('is_public')))
            ->when($request->query('category'), fn ($q, $cat) => $q->where('category', $cat));

        $this->applyDateRange($query, $request, 'start_date');
        $this->applySort($query, $request, ['start_date', 'order', 'created_at']);

        return $this->paginate($query, $request, TimelineEventResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $event = TimelineEvent::find($id);

        return $event ? $this->success(new TimelineEventResource($event)) : $this->notFound();
    }

    public function store(StoreTimelineEventRequest $request): JsonResponse
    {
        $event = TimelineEvent::create($request->validated());

        return $this->success(new TimelineEventResource($event), 201);
    }

    public function update(UpdateTimelineEventRequest $request, int $id): JsonResponse
    {
        $event = TimelineEvent::find($id);
        if (! $event) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $event);
        $event->update($request->validated());

        return $this->success(new TimelineEventResource($event));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $event = TimelineEvent::find($id);
        if (! $event) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $event);
        $event->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
