<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner' => $this->owner,
            'adage' => $this->adage,
            'keywords' => $this->keywords,
            'language' => $this->whenLoaded('language', fn () => $this->language ? ['id' => $this->language->id, 'code' => $this->language->code, 'name' => $this->language->name] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
