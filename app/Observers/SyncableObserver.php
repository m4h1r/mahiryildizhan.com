<?php

namespace App\Observers;

use App\Contracts\SyncSource;
use App\Models\SyncChange;

/**
 * Sync v2 (S2): appends a change-log row for every synced model write.
 *
 * `saved` covers create, update and restore (restore() calls save()), so a
 * separate `restored` listener would double-log — it is intentionally absent.
 * `deleted` covers both soft delete and force delete (tombstone).
 */
class SyncableObserver
{
    public function saved(SyncSource $model): void
    {
        $this->log($model, 'upsert');
    }

    public function deleted(SyncSource $model): void
    {
        $this->log($model, 'delete');
    }

    private function log(SyncSource $model, string $op): void
    {
        SyncChange::create([
            'entity' => $model::syncEntity(),
            'record_id' => $model->syncRecordId(),
            'op' => $op,
            'version' => $model->syncVersion(),
        ]);
    }
}
