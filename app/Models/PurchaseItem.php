<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'cost_try',
        'time_cost_hours',
        'is_bucketlist',
        'is_grocery',
        'is_completed',
        'completed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_bucketlist' => 'boolean',
            'is_grocery' => 'boolean',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'cost_try' => 'decimal:2',
            'time_cost_hours' => 'decimal:2',
        ];
    }
}
