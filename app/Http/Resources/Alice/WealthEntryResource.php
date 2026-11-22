<?php

namespace App\Http\Resources\Alice;

use App\Models\WealthEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WealthEntry
 */
class WealthEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'kind' => $this->kind,
            'label' => $this->label,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'as_of' => $this->as_of->toDateString(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
