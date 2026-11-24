<?php

namespace App\Http\Controllers\Alice;

use App\Models\SyncChange;
use App\Support\Sync\SyncRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Sync v2 (S3): read side of the Alice bridge. Alice (Pi) advances a cursor over
 * /sync/changes, reconciles with /sync/digest, and repairs or bootstraps with
 * /sync/snapshot. Site DB is the single source of truth.
 */
class SyncController extends AliceController
{
    /** Hard ceiling for a single changes page. */
    private const MAX_LIMIT = 1000;

    /**
     * Cheap, uncached: the current cursor (max change id) and the contract version.
     */
    public function head(): JsonResponse
    {
        return response()->json([
            'api_version' => (string) config('alice.api_version'),
            'cursor' => (int) SyncChange::max('id'),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * The change feed after a cursor, ordered by change_id. An `upsert` carries the
     * current record; a `delete` carries null.
     */
    public function changes(Request $request): JsonResponse
    {
        $after = max(0, (int) $request->query('after', 0));
        $limit = min(max((int) $request->query('limit', 500), 1), self::MAX_LIMIT);

        // Fetch one extra row to detect has_more without a second query.
        $rows = SyncChange::query()
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $rows = $rows->take($limit);

        $data = $rows->map(function (SyncChange $change): array {
            $record = null;
            $updatedAt = null;

            if ($change->op === 'upsert' && SyncRegistry::has($change->entity)) {
                $model = SyncRegistry::find($change->entity, $change->record_id);
                if ($model instanceof Model) {
                    $record = SyncRegistry::toRecord($change->entity, $model);
                    // The resource already carries updated_at as an ISO-8601 string.
                    $updatedAt = $record['updated_at'] ?? null;
                }
            }

            return [
                'change_id' => $change->id,
                'entity' => $change->entity,
                'id' => (int) $change->record_id,
                'op' => $change->op,
                'version' => $change->version,
                'updated_at' => $updatedAt,
                'record' => $record,
            ];
        })->all();

        $nextCursor = $rows->isNotEmpty() ? (int) $rows->last()->id : $after;

        return response()->json([
            'data' => $data,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ]);
    }

    /**
     * Per-entity fingerprint for drift detection. `hash` is sha1 over the non-deleted
     * "<id>:<version>" lines, string-ascending sorted and newline-joined (sha1('')
     * when the entity is empty).
     */
    public function digest(): JsonResponse
    {
        $entities = [];

        foreach (SyncRegistry::entities() as $entity) {
            $modelClass = SyncRegistry::modelClass($entity);

            /** @var Collection<int, \stdClass> $rows */
            $rows = $modelClass::query()->get(['id', 'version']);

            $lines = $rows->map(fn ($row): string => $row->id.':'.$row->version)->all();
            sort($lines, SORT_STRING);

            $entities[$entity] = [
                'count' => count($lines),
                'max_version' => (int) ($rows->max('version') ?? 0),
                'hash' => sha1(implode("\n", $lines)),
            ];
        }

        return response()->json(['entities' => $entities]);
    }

    /**
     * Full paginated dump of one entity's current (non-deleted) records for initial
     * setup or mismatch repair, plus the head cursor captured at the start.
     */
    public function snapshot(Request $request): JsonResponse
    {
        $entity = $request->query('entity');

        if (! SyncRegistry::has(is_string($entity) ? $entity : null)) {
            return response()->json([
                'error' => ['code' => 'unknown_entity', 'message' => 'Bilinmeyen entity'],
            ], 422);
        }

        /** @var string $entity */
        $cursor = (int) SyncChange::max('id');
        $perPage = min(max((int) $request->query('per_page', 200), 1), self::MAX_LIMIT);

        $result = SyncRegistry::modelClass($entity)::query()
            ->with(SyncRegistry::relations($entity))
            ->orderBy('id')
            ->paginate($perPage);

        $data = collect($result->items())
            ->map(fn (Model $model) => SyncRegistry::toRecord($entity, $model))
            ->all();

        return response()->json([
            'data' => $data,
            'cursor' => $cursor,
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
            'has_more' => $result->hasMorePages(),
        ]);
    }
}
