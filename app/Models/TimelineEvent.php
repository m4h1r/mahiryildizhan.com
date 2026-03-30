<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'image',
        'icon',
        'color',
        'is_public',
        'category',
        'location',
        'tags',
        'metadata',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_public' => 'boolean',
            'tags' => 'array',
            'metadata' => 'array',
        ];
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
