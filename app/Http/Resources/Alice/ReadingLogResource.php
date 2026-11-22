<?php

namespace App\Http\Resources\Alice;

use App\Models\ReadingLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReadingLog
 */
class ReadingLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'book_title' => $this->book_title,
            'author' => $this->author,
            'pages' => $this->pages,
            'read_on' => $this->read_on->toDateString(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
