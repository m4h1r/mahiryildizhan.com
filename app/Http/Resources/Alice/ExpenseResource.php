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
            'expense_type' => $this->whenLoaded('expenseType', function () {
                $et = $this->resource->getRelation('expenseType');
                return $et ? ['id' => $et->id, 'name' => $et->name] : null;
            }),
            'currency' => $this->whenLoaded('currency', function () {
                $c = $this->resource->getRelation('currency');
                return $c ? ['id' => $c->id, 'code' => $c->code, 'symbol' => $c->symbol] : null;
            }),
            'stakeholder' => $this->whenLoaded('stakeholder', fn () => $this->stakeholder ? ['id' => $this->stakeholder->id, 'title' => $this->stakeholder->title] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
