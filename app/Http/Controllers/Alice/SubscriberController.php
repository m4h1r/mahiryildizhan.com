<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreSubscriberRequest;
use App\Http\Requests\Alice\UpdateSubscriberRequest;
use App\Http\Resources\Alice\SubscriberResource;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriberController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Subscriber::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('email', 'like', "%{$search}%"))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status));

        $this->applySort($query, $request, ['email', 'subscribed_at', 'created_at']);

        return $this->paginate($query, $request, SubscriberResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $subscriber = Subscriber::find($id);

        return $subscriber ? $this->success(new SubscriberResource($subscriber)) : $this->notFound();
    }

    public function store(StoreSubscriberRequest $request): JsonResponse
    {
        $exists = Subscriber::where('email', $request->email)->exists();
        if ($exists) {
            return $this->conflict('Bu e-posta adresi zaten kayıtlı');
        }

        $subscriber = Subscriber::create($request->validated());

        return $this->success(new SubscriberResource($subscriber), 201);
    }

    public function update(UpdateSubscriberRequest $request, int $id): JsonResponse
    {
        $subscriber = Subscriber::find($id);
        if (! $subscriber) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $subscriber);
        $subscriber->update($request->validated());

        return $this->success(new SubscriberResource($subscriber));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $subscriber = Subscriber::find($id);
        if (! $subscriber) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $subscriber);
        $subscriber->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
