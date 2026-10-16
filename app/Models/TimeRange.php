<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'starts_at',
        'ends_at',
        'label',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }
}
