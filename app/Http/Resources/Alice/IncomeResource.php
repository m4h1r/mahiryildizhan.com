<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'amount' => $this->amount,
            'amount_display' => number_format((float) $this->amount, 2, ',', '.') . ' ₺',
            'description' => $this->description,
            'income_source' => $this->whenLoaded('source', fn () => $this->source ? ['id' => $this->source->id, 'name' => $this->source->name] : null),
            'income_type' => $this->whenLoaded('type', fn () => $this->type ? ['id' => $this->type->id, 'name' => $this->type->name] : null),
            'currency' => $this->whenLoaded('currency', function () {
                $c = $this->resource->getRelation('currency');
                return $c ? ['id' => $c->id, 'code' => $c->code, 'symbol' => $c->symbol] : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
