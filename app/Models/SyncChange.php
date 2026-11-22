<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One append-only entry in the Sync v2 change log. `id` is the cursor Alice (Pi)
 * advances through; rows are never updated, only inserted.
 *
 * @property int $id
 * @property string $entity
 * @property string $record_id
 * @property string $op
 * @property int $version
 * @property Carbon $created_at
 */
class SyncChange extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['entity', 'record_id', 'op', 'version'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
