<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Intervention\Zodiac\Sign;
use Intervention\Zodiac\Western\Signs\Aquarius;
use Intervention\Zodiac\Western\Signs\Aries;
use Intervention\Zodiac\Western\Signs\Cancer;
use Intervention\Zodiac\Western\Signs\Capricorn;
use Intervention\Zodiac\Western\Signs\Gemini;
use Intervention\Zodiac\Western\Signs\Leo;
use Intervention\Zodiac\Western\Signs\Libra;
use Intervention\Zodiac\Western\Signs\Pisces;
use Intervention\Zodiac\Western\Signs\Sagittarius;
use Intervention\Zodiac\Western\Signs\Scorpio;
use Intervention\Zodiac\Western\Signs\Taurus;
use Intervention\Zodiac\Western\Signs\Virgo;

class Person extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'name',
        'surname',
        'second_surname',
        'birthday',
        'deathday',
        'birth_place',
        'death_place',
        'father_id',
        'mother_id',
        'partner_id',
        'gender_id',
        'eye_color_id',
        'blood_type_id',
        'hair_color_id',
        'picture',
        'mobile',
        'email',
        'notes',
        'address',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'deathday' => 'date',
            'created_by' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function pictureUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->picture) {
                return '/storage/people/user.png';
            }
            if (str_starts_with($this->picture, 'http') || str_starts_with($this->picture, '/')) {
                return $this->picture;
            }

            return '/storage/people/'.$this->picture;
        });
    }

    public function father()
    {
        return $this->belongsTo(self::class, 'father_id');
    }

    public function mother()
    {
        return $this->belongsTo(self::class, 'mother_id');
    }

    public function partner()
    {
        return $this->belongsTo(self::class, 'partner_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function eyeColor()
    {
        return $this->belongsTo(EyeColor::class);
    }

    public function bloodType()
    {
        return $this->belongsTo(BloodType::class);
    }

    public function hairColor()
    {
        return $this->belongsTo(HairColor::class);
    }

    public function isGenderMale(): bool
    {
        return strcasecmp((string) optional($this->gender)->name, 'male') === 0
            || strcasecmp((string) optional($this->gender)->name, 'erkek') === 0;
    }

    public function children()
    {
        $field = $this->isGenderMale() ? 'father_id' : 'mother_id';

        return $this->hasMany(self::class, $field)->orderBy('birthday');
    }

    public function allChildren()
    {
        return $this->allChildrenQuery()->get();
    }

    public function allChildrenQuery()
    {
        return self::query()
            ->where(function ($query): void {
                $query
                    ->where('father_id', $this->id)
                    ->orWhere('mother_id', $this->id);
            })
            ->orderBy('birthday')
            ->orderBy('id');
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'person_id');
    }

    public function incomes()
    {
        return $this->morphMany(Income::class, 'sourceable');
    }

    public function zodiacName(): ?string
    {
        if (! $this->birthday) {
            return null;
        }

        return Sign::fromDate($this->birthday)->localize('tr')->name();
    }

    public function zodiacSymbol(): ?string
    {
        if (! $this->birthday) {
            return null;
        }

        return match (Sign::fromDate($this->birthday)::class) {
            Aries::class => '♈︎',
            Taurus::class => '♉︎',
            Gemini::class => '♊︎',
            Cancer::class => '♋︎',
            Leo::class => '♌︎',
            Virgo::class => '♍︎',
            Libra::class => '♎︎',
            Scorpio::class => '♏︎',
            Sagittarius::class => '♐︎',
            Capricorn::class => '♑︎',
            Aquarius::class => '♒︎',
            Pisces::class => '♓︎',
            default => null,
        };
    }

    public function genderSymbol(): string
    {
        $name = strtolower((string) optional($this->gender)->name);

        return match (true) {
            str_contains($name, 'erkek'), str_contains($name, 'male') => '♂',
            str_contains($name, 'kad'), str_contains($name, 'female') => '♀',
            default => '⚧',
        };
    }

    public function fullName(): string
    {
        return trim((string) $this->name.' '.(string) $this->surname);
    }

    public function getBloodTypeAttribute(): ?string
    {
        $relation = $this->getRelationValue('bloodType');

        if ($relation instanceof BloodType) {
            return $relation->name;
        }

        if ($this->blood_type_id === null) {
            return null;
        }

        return $this->bloodType()->value('name');
    }
}
