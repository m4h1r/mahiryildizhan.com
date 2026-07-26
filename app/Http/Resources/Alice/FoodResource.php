<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'calories_per_100g' => $this->calories_per_100g,
            'carbs_per_100g' => $this->carbs_per_100g,
            'sugar_per_100g' => $this->sugar_per_100g,
            'protein_per_100g' => $this->protein_per_100g,
            'fat_per_100g' => $this->fat_per_100g,
            'unit_type' => $this->unit_type,
            'grams_per_unit' => $this->grams_per_unit,
            'vitamins' => $this->vitamins ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
