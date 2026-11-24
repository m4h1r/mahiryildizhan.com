<?php

namespace App\Models;

use App\Contracts\SyncSource;
use App\Traits\Syncable;
use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $version
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 */
class AgendaEvent extends Model implements SyncSource
{
    use HasFactory;
    use SoftDeletes;
    use Syncable;
    use TracksCreatedBy;

    public static function syncEntity(): string
    {
        return 'agenda_events';
    }

    protected $fillable = [
        'title', 'starts_at', 'ends_at', 'all_day', 'location', 'notes', 'source_ref', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'created_by' => 'integer',
        ];
    }
}
