<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A contact channel of a person: extra phone / e-mail or a social profile.
 */
/**
 * @property int $id
 * @property int $person_id
 * @property string $type
 * @property string $value
 * @property string|null $label
 * @property bool $is_primary
 */
class PersonChannel extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = ['person_id', 'type', 'value', 'label', 'is_primary', 'created_by'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function typeLabel(): string
    {
        return __(config("crm.channel_types.{$this->type}", ucfirst((string) $this->type)));
    }

    /**
     * Clickable target for the channel, or null when the value is not linkable.
     */
    public function href(): ?string
    {
        $value = trim((string) $this->value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $digits = preg_replace('/[^\d+]/', '', $value);
        $handle = ltrim($value, '@');

        return match ($this->type) {
            'phone' => 'tel:'.$digits,
            'email' => 'mailto:'.$value,
            'whatsapp' => 'https://wa.me/'.ltrim((string) $digits, '+'),
            'telegram' => 'https://t.me/'.$handle,
            'instagram' => 'https://instagram.com/'.$handle,
            'x' => 'https://x.com/'.$handle,
            'linkedin' => 'https://www.linkedin.com/in/'.$handle,
            'facebook' => 'https://facebook.com/'.$handle,
            'tiktok' => 'https://www.tiktok.com/@'.$handle,
            'youtube' => 'https://www.youtube.com/@'.$handle,
            'github' => 'https://github.com/'.$handle,
            'website' => 'https://'.$value,
            default => null,
        };
    }
}
