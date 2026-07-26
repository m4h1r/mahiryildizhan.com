<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'food_id' => $this->food_id,
            'food' => $this->whenLoaded('food', fn () => $this->food ? [
                'id' => $this->food->id,
                'name' => $this->food->name,
            ] : null),
            'consumed_on' => $this->consumed_on?->toDateString(),
            'quantity' => $this->quantity,
            'unit' => $this->whenLoaded('food', fn () => $this->food?->unit_type === 'piece' ? 'adet' : 'gram'),
            'calories' => round($this->calories(), 1),
            'carbs' => round($this->carbs(), 1),
            'sugar' => round($this->sugar(), 1),
            'protein' => round($this->protein(), 1),
            'fat' => round($this->fat(), 1),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
