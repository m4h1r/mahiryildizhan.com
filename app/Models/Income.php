<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'date',
        'income_source_id',
        'income_type_id',
        'currency_id',
        'amount',
        'hours',
        'description',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'hours' => 'decimal:2',
        ];
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
