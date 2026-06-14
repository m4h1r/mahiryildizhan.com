<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExpenseType;
use App\Models\IncomeSource;
use App\Models\IncomeType;
use App\Models\InteractionType;
use App\Models\Stakeholder;

class AliceLookupService
{
    /**
     * Resolve a stakeholder by name or ID.
     * If not found, auto-creates with auto_created=true flag (returned via response attribute).
     */
    public function resolveStakeholder(string|int $nameOrId): array
    {
        if (is_numeric($nameOrId)) {
            $model = Stakeholder::find($nameOrId);

            return ['id' => $model?->id, 'auto_created' => false, 'model' => $model];
        }

        $model = Stakeholder::where('title', 'like', "%{$nameOrId}%")
            ->orWhere('name', 'like', "%{$nameOrId}%")
            ->first();

        if ($model) {
            return ['id' => $model->id, 'auto_created' => false, 'model' => $model];
        }

        // Auto-create
        $model = Stakeholder::create([
            'title' => $nameOrId,
            'name' => $nameOrId,
            'surname' => '',
            'company_type' => 'Individual',
            'country' => 'TR',
            'status' => 'Active',
        ]);

        return ['id' => $model->id, 'auto_created' => true, 'model' => $model];
    }

    public function resolveCurrency(string|int $codeOrId): ?Currency
    {
        if (is_numeric($codeOrId)) {
            return Currency::find($codeOrId);
        }

        return Currency::where('code', strtoupper($codeOrId))
            ->orWhere('name', 'like', "%{$codeOrId}%")
            ->first();
    }

    public function resolveExpenseType(string|int $nameOrId): ?ExpenseType
    {
        if (is_numeric($nameOrId)) {
            return ExpenseType::find($nameOrId);
        }

        return ExpenseType::where('name', 'like', "%{$nameOrId}%")->first();
    }

    public function resolveIncomeSource(string|int $nameOrId): ?IncomeSource
    {
        if (is_numeric($nameOrId)) {
            return IncomeSource::find($nameOrId);
        }

        return IncomeSource::where('name', 'like', "%{$nameOrId}%")->first();
    }

    public function resolveIncomeType(string|int $nameOrId): ?IncomeType
    {
        if (is_numeric($nameOrId)) {
            return IncomeType::find($nameOrId);
        }

        return IncomeType::where('name', 'like', "%{$nameOrId}%")->first();
    }

    public function resolveInteractionType(string|int $nameOrId): ?InteractionType
    {
        if (is_numeric($nameOrId)) {
            return InteractionType::find($nameOrId);
        }

        return InteractionType::where('name', 'like', "%{$nameOrId}%")->first();
    }
}
