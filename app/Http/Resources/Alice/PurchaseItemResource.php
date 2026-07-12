<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'cost_try' => $this->cost_try,
            'cost_display' => $this->cost_try ? number_format((float) $this->cost_try, 2, ',', '.').' ₺' : null,
            'time_cost_hours' => $this->time_cost_hours,
            'is_bucketlist' => $this->is_bucketlist,
            'is_grocery' => $this->is_grocery,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
