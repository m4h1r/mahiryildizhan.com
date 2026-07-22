<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), ['updated_at']);

        if ($changes !== []) {
            $this->log('updated', $model, $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, ['id' => $model->getKey()]);
    }

    private function log(string $action, Model $model, array $changes): void
    {
        ActivityLog::query()->create([
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'action' => $action,
            'user_id' => auth()->id(),
            'changes' => $changes,
        ]);
    }
}
