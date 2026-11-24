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
 * @property Carbon $read_on
 */
class ReadingLog extends Model implements SyncSource
{
    use HasFactory;
    use SoftDeletes;
    use Syncable;
    use TracksCreatedBy;

    public static function syncEntity(): string
    {
        return 'reading_logs';
    }

    protected $fillable = [
        'book_title', 'author', 'pages', 'read_on', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'pages' => 'integer',
            'read_on' => 'date',
            'created_by' => 'integer',
        ];
    }
}
