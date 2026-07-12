<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'disk',
        'mime_type',
        'extension',
        'checksum',
        'size',
        'width',
        'height',
        'type',
        'alt',
        'caption',
        'thumbnail_path',
        'webp_path',
        'variant_paths',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'type' => 'integer',
            'variant_paths' => 'array',
        ];
    }

    public function url(string $size = 'original'): string
    {
        $path = match (strtolower($size)) {
            'thumbnail' => $this->thumbnail_path ?? $this->path,
            'webp' => $this->webp_path ?? $this->path,
            'sm', 'md', 'lg' => $this->variant_paths[strtolower($size)] ?? $this->webp_path ?? $this->path,
            default => $this->path,
        };

        return Storage::disk($this->disk)->url($path);
    }

    public function srcset(): ?string
    {
        if ($this->type !== 1) {
            return null;
        }

        $parts = [];
        $map = ['sm' => 320, 'md' => 640, 'lg' => 1200];

        foreach ($map as $key => $width) {
            if (! empty($this->variant_paths[$key])) {
                $parts[] = $this->url($key).' '.$width.'w';
            }
        }

        if (! empty($this->webp_path)) {
            $parts[] = $this->url('webp').' '.((int) ($this->width ?: 1920)).'w';
        }

        return $parts !== [] ? implode(', ', $parts) : null;
    }

    public function coverPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'cover_media_id');
    }
}
