<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        ActivityLog::query()->create([
            'model_type' => User::class,
            'model_id' => $event->user?->getKey(),
            'action' => 'login_failed',
            'user_id' => null,
            'changes' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'email' => $event->credentials['email'] ?? null,
            ],
        ]);
    }
}
