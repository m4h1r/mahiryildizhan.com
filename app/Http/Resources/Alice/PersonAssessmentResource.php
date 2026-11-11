<?php

namespace App\Http\Resources\Alice;

use App\Models\PersonAssessment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PersonAssessment
 */
class PersonAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'assessed_at' => $this->assessed_at?->toDateString(),
            'source' => $this->source,
            'overall_confidence' => $this->overall_confidence,
            'relationship_label' => $this->relationship_label,
            'big_five' => $this->big_five ?? (object) [],
            'relationship_bases' => $this->relationship_bases ?? (object) [],
            'metrics' => $this->metrics ?? (object) [],
            'attachment_style' => $this->attachment_style,
            'attachment_note' => $this->attachment_note,
            'values' => $this->values ?? (object) [],
            'dark_triad' => $this->dark_triad ?? (object) [],
            'dark_triad_note' => $this->dark_triad_note,
            'approach' => $this->approach ?? (object) [],
            'summary' => $this->summary,
            'notes' => $this->notes,
            'history_note' => $this->history_note,
            'source_message_count' => $this->source_message_count,
            'raw' => $this->when($request->boolean('with_raw'), fn () => $this->raw),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
