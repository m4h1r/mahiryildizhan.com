<?php

namespace App\Traits;

use App\Models\User;

trait TracksCreatedBy
{
    protected static function bootTracksCreatedBy(): void
    {
        static::creating(function ($model): void {
            if (! empty($model->created_by)) {
                return;
            }

            if (auth()->check()) {
                $model->created_by = auth()->id();

                return;
            }

            if (User::query()->whereKey(2)->exists()) {
                $model->created_by = 2;
            }
        });
    }
}
