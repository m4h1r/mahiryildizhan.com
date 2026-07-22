<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        ActivityLog::query()->create([
            'model_type' => $event->user::class,
            'model_id' => $event->user->getKey(),
            'action' => 'logout',
            'user_id' => $event->user->getKey(),
            'changes' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
