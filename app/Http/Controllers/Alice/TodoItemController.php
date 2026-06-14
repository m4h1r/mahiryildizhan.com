<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreTodoItemRequest;
use App\Http\Requests\Alice\UpdateTodoItemRequest;
use App\Http\Resources\Alice\TodoItemResource;
use App\Models\TodoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoItemController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = TodoItem::query()
            ->when($request->query('q'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->when($request->has('is_completed'), fn ($q) => $q->where('is_completed', $request->boolean('is_completed')))
            ->when($request->has('is_bucketlist'), fn ($q) => $q->where('is_bucketlist', $request->boolean('is_bucketlist')))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applyDateRange($query, $request, 'due_date');
        $this->applySort($query, $request, ['title', 'due_date', 'cost_try', 'created_at']);

        return $this->paginate($query, $request, TodoItemResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $item = TodoItem::find($id);

        return $item ? $this->success(new TodoItemResource($item)) : $this->notFound();
    }

    public function store(StoreTodoItemRequest $request): JsonResponse
    {
        $item = TodoItem::create($request->validated());

        return $this->success(new TodoItemResource($item), 201);
    }

    public function update(UpdateTodoItemRequest $request, int $id): JsonResponse
    {
        $item = TodoItem::find($id);
        if (! $item) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $item);
        $data = $request->validated();

        // Auto set completed_at when marking complete
        if (isset($data['is_completed']) && $data['is_completed'] && ! $item->is_completed) {
            $data['completed_at'] = now();
        }

        $item->update($data);

        return $this->success(new TodoItemResource($item));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = TodoItem::find($id);
        if (! $item) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $item);
        $item->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
