<?php

namespace App\Traits;

use App\Contracts\SyncSource;
use App\Observers\SyncableObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * Sync v2 (S2): makes a model a source of truth for Alice (Pi).
 *
 * - seeds `version` to 1 on insert and bumps it on every update;
 * - records a `sync_changes` row through {@see SyncableObserver} after each
 *   save (upsert) and delete.
 *
 * The using model must `implements SyncSource` and declare its own syncEntity().
 *
 * @mixin Model
 */
trait Syncable
{
    public static function bootSyncable(): void
    {
        // Eloquent does not hydrate the column default, so pin the opening version
        // in memory — otherwise the first update bumps from null and lands on 1 again.
        static::creating(function (Model $model): void {
            if ($model->getAttribute('version') === null) {
                $model->setAttribute('version', 1);
            }
        });

        static::saving(function (Model $model): void {
            // Bump on updates only; a fresh insert keeps the opening version of 1.
            // Skip when `version` was set explicitly so we never double-count.
            if ($model->exists && $model->isDirty() && ! $model->isDirty('version')) {
                $model->setAttribute('version', (int) ($model->getAttribute('version') ?? 0) + 1);
            }
        });

        // Delegate to the observer without Model::observe(), which cannot run while
        // the model is still booting. `saved` covers create/update/restore; `deleted`
        // covers soft and force delete.
        $observer = new SyncableObserver;

        static::saved(function (Model $model) use ($observer): void {
            if ($model instanceof SyncSource) {
                $observer->saved($model);
            }
        });

        static::deleted(function (Model $model) use ($observer): void {
            if ($model instanceof SyncSource) {
                $observer->deleted($model);
            }
        });
    }

    public function syncRecordId(): string
    {
        return (string) $this->getKey();
    }

    public function syncVersion(): int
    {
        return (int) ($this->getAttribute('version') ?? 1);
    }
}
