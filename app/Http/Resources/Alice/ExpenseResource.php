<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'tax' => $this->tax,
            'total' => $this->total,
            'total_display' => number_format((float) $this->total, 2, ',', '.') . ' ₺',
            'company_expense' => $this->company_expense,
            'paid_by_others' => $this->paid_by_others,
            'expense_type' => $this->whenLoaded('expenseType', fn () => ['id' => $this->expenseType->id, 'name' => $this->expenseType->name]),
            'currency' => $this->whenLoaded('currency', fn () => ['id' => $this->currency->id, 'code' => $this->currency->code, 'symbol' => $this->currency->symbol]),
            'stakeholder' => $this->whenLoaded('stakeholder', fn () => $this->stakeholder ? ['id' => $this->stakeholder->id, 'title' => $this->stakeholder->title] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
