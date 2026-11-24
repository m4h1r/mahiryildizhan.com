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
 * @property Carbon $as_of
 */
class WealthEntry extends Model implements SyncSource
{
    use HasFactory;
    use SoftDeletes;
    use Syncable;
    use TracksCreatedBy;

    public static function syncEntity(): string
    {
        return 'wealth_entries';
    }

    protected $fillable = [
        'kind', 'label', 'amount', 'currency', 'as_of', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'as_of' => 'date',
            'created_by' => 'integer',
        ];
    }
}
