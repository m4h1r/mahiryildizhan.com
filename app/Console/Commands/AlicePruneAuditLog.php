<?php

namespace App\Console\Commands;

use App\Models\AliceAuditLog;
use App\Models\IdempotencyKey;
use Illuminate\Console\Command;

class AlicePruneAuditLog extends Command
{
    protected $signature = 'alice:prune-audit-log {--days= : Kaç günden eski kayıtlar silinsin (varsayılan: config)}';

    protected $description = 'Eski Alice audit log kayıtlarını ve süresi dolmuş idempotency key\'lerini temizle';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('alice.audit_retention_days', 365));
        $cutoff = now()->subDays($days);

        $deletedAudit = AliceAuditLog::where('created_at', '<', $cutoff)->delete();
        $deletedKeys = IdempotencyKey::where('expires_at', '<', now())->delete();

        $this->info("Audit log: {$deletedAudit} kayıt silindi ({$days} günden eski).");
        $this->info("Idempotency keys: {$deletedKeys} kayıt silindi (süresi dolmuş).");

        return self::SUCCESS;
    }
}
