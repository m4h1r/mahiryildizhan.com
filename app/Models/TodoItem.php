<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodoItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'cost_try',
        'time_cost_hours',
        'due_date',
        'is_bucketlist',
        'is_completed',
        'completed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_bucketlist' => 'boolean',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'cost_try' => 'decimal:2',
            'time_cost_hours' => 'decimal:2',
        ];
    }
}
