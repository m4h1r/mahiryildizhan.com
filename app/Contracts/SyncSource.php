<?php

namespace App\Contracts;

use App\Traits\Syncable;

/**
 * A model that is a source of truth for Alice (Pi) Sync v2. Implemented via the
 * {@see Syncable} trait; each model declares its own entity name.
 */
interface SyncSource
{
    /** Alice entity name: people, assessments, todo-items, projects … */
    public static function syncEntity(): string;

    public function syncRecordId(): string;

    public function syncVersion(): int;
}
