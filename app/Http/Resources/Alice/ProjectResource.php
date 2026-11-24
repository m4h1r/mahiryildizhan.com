<?php

namespace App\Http\Resources\Alice;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'slug' => $this->slug,
            'name' => $this->name,
            'area' => $this->area,
            'status' => $this->status,
            'progress' => $this->progress,
            'note' => $this->note,
            'due' => $this->due?->toDateString(),
            'due_postponed' => $this->due_postponed,
            'idea_date' => $this->idea_date?->toDateString(),
            'local_path' => $this->local_path,
            'repo_url' => $this->repo_url,
            'scope_ref' => $this->scope_ref,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
