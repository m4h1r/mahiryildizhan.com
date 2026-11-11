<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * A face sample of a person for face recognition: a private image and/or an
 * embedding vector. Biometric data (KVKK special category):
 *  - the image lives on a private disk and is only streamed to admins / Alice;
 *  - the embedding is encrypted at rest and hidden from toArray()/JSON, so it
 *    never lands in audit logs or idempotency replays by accident.
 */
/**
 * @property int $id
 * @property int $person_id
 * @property string|null $image_path
 * @property array<int, float>|null $embedding
 * @property string|null $embedding_model
 * @property int|null $embedding_dim
 * @property float|null $quality
 * @property bool $is_primary
 * @property string $source
 * @property Carbon|null $captured_at
 */
class PersonFace extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'person_id', 'image_path', 'embedding', 'embedding_model', 'embedding_dim',
        'bbox', 'landmarks', 'quality', 'is_primary', 'source', 'captured_at', 'created_by',
    ];

    protected $hidden = ['embedding', 'image_path'];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'bbox' => 'array',
            'landmarks' => 'array',
            'quality' => 'float',
            'is_primary' => 'boolean',
            'embedding_dim' => 'integer',
            'captured_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (PersonFace $face): void {
            $face->deleteImageFile();
        });
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public static function disk(): string
    {
        return (string) config('crm.faces.disk', 'local');
    }

    public function hasImage(): bool
    {
        return $this->image_path !== null && Storage::disk(self::disk())->exists($this->image_path);
    }

    public function hasEmbedding(): bool
    {
        return $this->embedding_dim !== null && $this->embedding_dim > 0;
    }

    public function deleteImageFile(): void
    {
        if ($this->image_path) {
            Storage::disk(self::disk())->delete($this->image_path);
        }
    }
}
