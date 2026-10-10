<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'date',
        'income_source_id',
        'income_type_id',
        'currency_id',
        'amount',
        'hours',
        'description',
        'user_id',
        'created_by',
        'sourceable_type',
        'sourceable_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'hours' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hourlyRate(): ?float
    {
        if (! $this->hours || (float) $this->hours <= 0) {
            return null;
        }

        return (float) $this->amount / (float) $this->hours;
    }

    public function source()
    {
        return $this->belongsTo(IncomeSource::class, 'income_source_id');
    }

    public function sourceable()
    {
        return $this->morphTo();
    }

    public function sourceableLabel(): ?string
    {
        $sourceable = $this->sourceable;

        if ($sourceable instanceof Person) {
            return $sourceable->fullName();
        }

        if ($sourceable instanceof Stakeholder) {
            return $sourceable->title ?: trim(((string) $sourceable->name).' '.((string) $sourceable->surname));
        }

        return null;
    }

    public function type()
    {
        return $this->belongsTo(IncomeType::class, 'income_type_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
