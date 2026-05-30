<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'achieved_at',
        'milestoneable_type',
        'milestoneable_id',
    ];

    protected function casts(): array
    {
        return [
            'achieved_at' => 'datetime',
        ];
    }

    public function milestoneable(): MorphTo
    {
        return $this->morphTo();
    }
}
