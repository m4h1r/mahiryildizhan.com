<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'method',
        'path',
        'status_code',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'response_body' => 'array',
        'status_code' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
