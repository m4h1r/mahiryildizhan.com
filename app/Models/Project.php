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
 * @property Carbon|null $due
 * @property Carbon|null $idea_date
 */
class Project extends Model implements SyncSource
{
    use HasFactory;
    use SoftDeletes;
    use Syncable;
    use TracksCreatedBy;

    public static function syncEntity(): string
    {
        return 'projects';
    }

    protected $fillable = [
        'slug', 'name', 'area', 'status', 'progress', 'note', 'due',
        'due_postponed', 'idea_date', 'local_path', 'repo_url', 'scope_ref', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'due' => 'date',
            'due_postponed' => 'boolean',
            'idea_date' => 'date',
            'created_by' => 'integer',
        ];
    }
}
