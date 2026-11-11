<?php

namespace App\Http\Resources\Alice;

use App\Models\PersonChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PersonChannel
 */
class PersonChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'type' => $this->type,
            'value' => $this->value,
            'label' => $this->label,
            'is_primary' => (bool) $this->is_primary,
            'url' => $this->href(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
