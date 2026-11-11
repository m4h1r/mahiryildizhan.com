<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
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

/**
 * @property Carbon|null $birthday
 * @property Carbon|null $deathday
 * @property Carbon|null $met_at
 * @property int|null $birth_day
 * @property int|null $birth_month
 * @property int|null $birth_year
 * @property array<int, string>|null $relationship_bases
 * @property array<int, string>|null $interests
 * @property array<int, string>|null $hobbies
 * @property array<int, string>|null $tendencies
 * @property-read string $picture_url
 */
class Person extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'name',
        'surname',
        'second_surname',
        'nickname',
        'birthday',
        'birth_day',
        'birth_month',
        'birth_year',
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
        'relationship_scope',
        'relationship_label',
        'relationship_bases',
        'company',
        'job_title',
        'stakeholder_id',
        'character_summary',
        'interests',
        'hobbies',
        'tendencies',
        'met_at',
        'met_how',
        'alice_slug',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'deathday' => 'date',
            'birth_day' => 'integer',
            'birth_month' => 'integer',
            'birth_year' => 'integer',
            'relationship_bases' => 'array',
            'interests' => 'array',
            'hobbies' => 'array',
            'tendencies' => 'array',
            'met_at' => 'date',
            'created_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Keep the birth parts and the full `birthday` in sync. Parts win when
        // both change; a full date is derived only when day, month and year exist.
        static::saving(function (Person $person): void {
            $partsDirty = $person->isDirty(['birth_day', 'birth_month', 'birth_year']);

            if (! $partsDirty && $person->isDirty('birthday')) {
                $date = $person->birthday ? Carbon::parse($person->birthday) : null;
                $person->birth_day = $date?->day;
                $person->birth_month = $date?->month;
                $person->birth_year = $date?->year;

                return;
            }

            if ($partsDirty) {
                $d = $person->birth_day;
                $m = $person->birth_month;
                $y = $person->birth_year;
                $person->birthday = ($d && $m && $y && checkdate($m, $d, $y))
                    ? Carbon::createFromDate($y, $m, $d)->startOfDay()
                    : null;
            }
        });

        // D9: child tables have no DB cascade (people use SoftDeletes), so a hard
        // delete clears them here — including the private face image files.
        static::forceDeleting(function (Person $person): void {
            PersonFace::withTrashed()->where('person_id', $person->id)->get()->each->forceDelete();
            PersonChannel::withTrashed()->where('person_id', $person->id)->forceDelete();
            PersonAssessment::withTrashed()->where('person_id', $person->id)->forceDelete();
        });
    }

    /**
     * @return HasMany<PersonChannel, $this>
     */
    public function channels(): HasMany
    {
        return $this->hasMany(PersonChannel::class)->orderByDesc('is_primary')->orderBy('type');
    }

    /**
     * @return HasMany<PersonFace, $this>
     */
    public function faces(): HasMany
    {
        return $this->hasMany(PersonFace::class)->orderByDesc('is_primary')->latest('id');
    }

    /**
     * @return HasOne<PersonFace, $this>
     */
    public function primaryFace(): HasOne
    {
        return $this->hasOne(PersonFace::class)->ofMany(['is_primary' => 'max', 'id' => 'max']);
    }

    /**
     * @return HasMany<PersonAssessment, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(PersonAssessment::class)->orderByDesc('assessed_at')->orderByDesc('id');
    }

    /**
     * @return HasOne<PersonAssessment, $this>
     */
    public function latestAssessment(): HasOne
    {
        return $this->hasOne(PersonAssessment::class)->ofMany(['assessed_at' => 'max', 'id' => 'max']);
    }

    /**
     * @return BelongsTo<Stakeholder, $this>
     */
    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class);
    }

    /**
     * Avatar for admin screens: the public picture, else the primary face image
     * (streamed through the admin-only route, never copied to a public disk).
     */
    public function avatarUrl(): string
    {
        if ($this->picture) {
            return $this->picture_url;
        }

        $face = $this->relationLoaded('primaryFace') ? $this->primaryFace : $this->primaryFace()->first();
        if ($face && $face->image_path) {
            return route('admin.people.faces.image', [$this->id, $face->id]);
        }

        return $this->picture_url;
    }

    public function scopeLabel(): ?string
    {
        return $this->relationship_scope
            ? __(config('crm.relationship_scopes.'.$this->relationship_scope, $this->relationship_scope))
            : null;
    }

    /**
     * Birth date as far as it is known: "12 Mart 1990", "12 Mart", "1990".
     */
    public function birthLabel(): ?string
    {
        $d = $this->birth_day;
        $m = $this->birth_month;
        $y = $this->birth_year;

        if ($d && $m && checkdate($m, $d, $y ?: 2000)) {
            $date = Carbon::createFromDate($y ?: 2000, $m, $d);

            return $y ? $date->translatedFormat('j F Y') : $date->translatedFormat('j F');
        }
        if ($m && $y) {
            return Carbon::createFromDate($y, $m, 1)->translatedFormat('F Y');
        }

        return $y ? (string) $y : null;
    }

    public function age(): ?int
    {
        if (! $this->birth_year) {
            return null;
        }
        $end = $this->deathday ?? now();
        if ($this->birth_month && $this->birth_day && checkdate($this->birth_month, $this->birth_day, $this->birth_year)) {
            return (int) floor(Carbon::createFromDate($this->birth_year, $this->birth_month, $this->birth_day)->diffInYears($end, true));
        }

        return (int) $end->year - $this->birth_year;
    }

    /**
     * Date used for zodiac: works with day + month even when the year is unknown.
     */
    private function zodiacDate(): ?Carbon
    {
        if ($this->birth_day && $this->birth_month && checkdate($this->birth_month, $this->birth_day, 2000)) {
            return Carbon::createFromDate(2000, $this->birth_month, $this->birth_day);
        }

        return $this->birthday ? Carbon::parse($this->birthday) : null;
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
        $date = $this->zodiacDate();

        return $date ? Sign::fromDate($date)->localize('tr')->name() : null;
    }

    public function zodiacSymbol(): ?string
    {
        $date = $this->zodiacDate();
        if (! $date) {
            return null;
        }

        return match (Sign::fromDate($date)::class) {
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
