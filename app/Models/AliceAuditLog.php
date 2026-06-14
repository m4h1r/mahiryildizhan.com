<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliceAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'alice_audit_log';

    protected $fillable = [
        'action',
        'table_name',
        'record_id',
        'old_data',
        'new_data',
        'source',
        'ip',
        'idempotency_key',
        'user_agent',
        'dry_run',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'dry_run' => 'boolean',
        'record_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
