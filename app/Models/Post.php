<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover',
        'cover_media_id',
        'category_id',
        'language_id',
        'user_id',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_image',
        'schema_type',
        'reading_time',
        'word_count',
        'view_count',
        'unique_view_count',
        'share_count',
        'like_count',
        'save_count',
        'status',
        'published_at',
        'publish_date',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'published_at' => 'datetime',
            'publish_date' => 'date',
        ];
    }

    protected $appends = [
        'cover_url',
    ];

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function language()
    {
        return $this->belongsTo(PostLanguage::class, 'language_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coverMedia()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder): void {
                $builder
                    ->where('published', true)
                    ->orWhere('status', 'published');
            })
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('publish_date')
                    ->orWhereDate('publish_date', '<=', now()->toDateString());
            });
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (($this->relationLoaded('coverMedia') || $this->cover_media_id) && $this->coverMedia) {
            return $this->coverMedia->url('webp');
        }

        if (empty($this->cover)) {
            return null;
        }

        if (str_starts_with($this->cover, 'http://') || str_starts_with($this->cover, 'https://')) {
            return $this->cover;
        }

        $path = str_starts_with($this->cover, 'covers/') ? $this->cover : 'covers/'.$this->cover;

        return Storage::disk('public')->url($path);
    }
}
