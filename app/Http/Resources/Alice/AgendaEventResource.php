<?php

namespace App\Http\Resources\Alice;

use App\Models\AgendaEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AgendaEvent
 */
class AgendaEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'title' => $this->title,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'all_day' => $this->all_day,
            'location' => $this->location,
            'notes' => $this->notes,
            'source_ref' => $this->source_ref,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
