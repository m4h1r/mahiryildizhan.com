<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Income;
use Illuminate\Support\Arr;

class IncomeObserver
{
    public function created(Income $income): void
    {
        $this->log('created', $income, $income->getAttributes());
    }

    public function updated(Income $income): void
    {
        $changes = Arr::except($income->getChanges(), ['updated_at']);

        if ($changes !== []) {
            $this->log('updated', $income, $changes);
        }
    }

    public function deleted(Income $income): void
    {
        $this->log('deleted', $income, ['id' => $income->id]);
    }

    private function log(string $action, Income $income, array $changes): void
    {
        ActivityLog::query()->create([
            'model_type' => Income::class,
            'model_id' => $income->id,
            'action' => $action,
            'user_id' => auth()->id(),
            'changes' => $changes,
        ]);
    }
}
