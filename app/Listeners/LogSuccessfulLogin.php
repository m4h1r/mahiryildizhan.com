<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        ActivityLog::query()->create([
            'model_type' => $event->user::class,
            'model_id' => $event->user->getKey(),
            'action' => 'login',
            'user_id' => $event->user->getKey(),
            'changes' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
