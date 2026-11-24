<?php

namespace App\Support\Sync;

use App\Http\Resources\Alice\AgendaEventResource;
use App\Http\Resources\Alice\PersonAssessmentResource;
use App\Http\Resources\Alice\PersonResource;
use App\Http\Resources\Alice\ProjectResource;
use App\Http\Resources\Alice\ReadingLogResource;
use App\Http\Resources\Alice\TodoItemResource;
use App\Http\Resources\Alice\WealthEntryResource;
use App\Models\AgendaEvent;
use App\Models\Person;
use App\Models\PersonAssessment;
use App\Models\Project;
use App\Models\ReadingLog;
use App\Models\TodoItem;
use App\Models\WealthEntry;
use Illuminate\Database\Eloquent\Model;

/**
 * Sync v2 (S3): the map of Alice entity names to their model, API resource, and
 * the relations that must be eager-loaded so a synced `record` matches the
 * existing GET /{entity} output. S5 entities register here when they are built.
 */
class SyncRegistry
{
    /**
     * entity => [model class, resource class, eager-load relations].
     *
     * @var array<string, array{0: class-string<Model>, 1: class-string, 2: array<int, string>}>
     */
    private const MAP = [
        'people' => [Person::class, PersonResource::class, ['gender', 'bloodType', 'eyeColor', 'hairColor']],
        'assessments' => [PersonAssessment::class, PersonAssessmentResource::class, []],
        'todo-items' => [TodoItem::class, TodoItemResource::class, []],
        'projects' => [Project::class, ProjectResource::class, []],
        'agenda_events' => [AgendaEvent::class, AgendaEventResource::class, []],
        'wealth_entries' => [WealthEntry::class, WealthEntryResource::class, []],
        'reading_logs' => [ReadingLog::class, ReadingLogResource::class, []],
    ];

    /**
     * @return array<int, string>
     */
    public static function entities(): array
    {
        return array_keys(self::MAP);
    }

    public static function has(?string $entity): bool
    {
        return $entity !== null && array_key_exists($entity, self::MAP);
    }

    /**
     * @return class-string<Model>
     */
    public static function modelClass(string $entity): string
    {
        return self::MAP[$entity][0];
    }

    /**
     * @return array<int, string>
     */
    public static function relations(string $entity): array
    {
        return self::MAP[$entity][2];
    }

    /**
     * The current record for a change-log row, including soft-deleted rows so an
     * upsert that was later deleted can still be resolved. Null when hard-gone.
     */
    public static function find(string $entity, string $recordId): ?Model
    {
        $model = new (self::MAP[$entity][0]);

        return $model->newQueryWithoutScopes()
            ->with(self::relations($entity))
            ->find($recordId);
    }

    /**
     * Render a model through its Alice resource, matching GET /{entity} output.
     *
     * @return array<string, mixed>
     */
    public static function toRecord(string $entity, Model $model): array
    {
        $resourceClass = self::MAP[$entity][1];

        return (new $resourceClass($model))->resolve();
    }
}
