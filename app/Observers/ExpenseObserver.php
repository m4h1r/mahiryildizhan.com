<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Support\Arr;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $this->log('created', $expense, $expense->getAttributes());
    }

    public function updated(Expense $expense): void
    {
        $changes = Arr::except($expense->getChanges(), ['updated_at']);

        if ($changes !== []) {
            $this->log('updated', $expense, $changes);
        }
    }

    public function deleted(Expense $expense): void
    {
        $this->log('deleted', $expense, ['id' => $expense->id]);
    }

    private function log(string $action, Expense $expense, array $changes): void
    {
        ActivityLog::query()->create([
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'action' => $action,
            'user_id' => auth()->id(),
            'changes' => $changes,
        ]);
    }
}
