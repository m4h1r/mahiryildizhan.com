<?php

namespace App\Http\Resources\Alice;

use App\Models\PersonFace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The embedding is biometric data: it is only returned on GET requests that
 * ask for it with ?with_embedding=1, so it never ends up in write responses
 * (which the audit log and idempotency store keep).
 *
 * @mixin PersonFace
 */
class PersonFaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $withEmbedding = $request->isMethod('GET') && $request->boolean('with_embedding');

        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'has_image' => $this->image_path !== null,
            'image_url' => $this->image_path ? url("/api/v1/alice/people/{$this->person_id}/faces/{$this->id}/image") : null,
            'embedding_model' => $this->embedding_model,
            'embedding_dim' => $this->embedding_dim,
            'embedding' => $this->when($withEmbedding, fn () => $this->embedding),
            'bbox' => $this->bbox,
            'landmarks' => $this->landmarks,
            'quality' => $this->quality,
            'is_primary' => (bool) $this->is_primary,
            'source' => $this->source,
            'captured_at' => $this->captured_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
