<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'text_color' => $this->text_color,
            'text_size' => $this->text_size,
            'connections_from' => $this->whenLoaded('linksFrom', fn () => $this->linksFrom->map(fn ($c) => ['to_node_id' => $c->node_to_id])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
