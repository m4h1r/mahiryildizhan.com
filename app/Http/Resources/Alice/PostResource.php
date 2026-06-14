<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'published' => $this->published,
            'published_at' => $this->published_at?->toIso8601String(),
            'reading_time' => $this->reading_time,
            'word_count' => $this->word_count,
            'view_count' => $this->view_count,
            'category' => $this->whenLoaded('category', fn () => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null),
            'language' => $this->whenLoaded('language', fn () => $this->language ? ['id' => $this->language->id, 'code' => $this->language->code, 'name' => $this->language->name] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
