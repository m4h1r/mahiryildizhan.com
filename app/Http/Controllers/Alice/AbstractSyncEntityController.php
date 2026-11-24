<?php

namespace App\Http\Controllers\Alice;

use App\Contracts\SyncSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sync v2 (S5): shared CRUD for the Pi-managed entities (projects, agenda events,
 * wealth entries, reading logs). Carries the full write contract: Idempotency-Key
 * on POST, If-Match version guard on PATCH/DELETE, soft-delete tombstones.
 */
abstract class AbstractSyncEntityController extends AliceController
{
    /** @return class-string<Model&SyncSource> */
    abstract protected function modelClass(): string;

    /** @return class-string<JsonResource> */
    abstract protected function resourceClass(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function rules(bool $partial, ?int $id): array;

    /** @var array<int, string> */
    protected array $sortable = ['created_at'];

    public function index(Request $request): JsonResponse
    {
        $query = $this->modelClass()::query();

        $this->applySort($query, $request, $this->sortable);

        return $this->paginate($query, $request, $this->resourceClass());
    }

    public function show(int $id): JsonResponse
    {
        $model = $this->modelClass()::query()->find($id);

        return $model ? $this->success($this->resource($model)) : $this->notFound();
    }

    public function store(Request $request): JsonResponse
    {
        if ($conflict = $this->requireIdempotencyKey($request)) {
            return $conflict;
        }

        $model = $this->modelClass()::create($this->validateAlice($request, $this->rules(false, null)));

        return $this->success($this->resource($model), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $model = $this->modelClass()::query()->find($id);
        if (! $model) {
            return $this->notFound();
        }

        if ($conflict = $this->guardVersion($request, (int) $model->getAttribute('version'), $this->resource($model)->resolve())) {
            return $conflict;
        }

        $this->storeAuditOldData($request, $model);
        $model->update($this->validateAlice($request, $this->rules(true, $id)));

        return $this->success($this->resource($model));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $model = $this->modelClass()::query()->find($id);
        if (! $model) {
            return $this->notFound();
        }

        if ($conflict = $this->guardVersion($request, (int) $model->getAttribute('version'), $this->resource($model)->resolve())) {
            return $conflict;
        }

        $this->storeAuditOldData($request, $model);
        $model->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    private function resource(Model $model): JsonResource
    {
        $class = $this->resourceClass();

        return new $class($model);
    }
}
