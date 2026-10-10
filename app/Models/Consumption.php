<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumption extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'food_id',
        'consumed_on',
        'quantity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'consumed_on' => 'date',
            'quantity' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function food(): BelongsTo
    {
        // withTrashed: a soft-deleted food must still resolve here so past
        // consumption entries keep their correct calorie/macro calculations.
        return $this->belongsTo(Food::class)->withTrashed();
    }

    public function gramsConsumed(): float
    {
        if ($this->food->unit_type === 'piece') {
            return (float) $this->quantity * (float) $this->food->grams_per_unit;
        }

        return (float) $this->quantity;
    }

    public function calories(): float
    {
        return $this->gramsConsumed() / 100 * (float) $this->food->calories_per_100g;
    }

    public function carbs(): float
    {
        return $this->gramsConsumed() / 100 * (float) $this->food->carbs_per_100g;
    }

    public function sugar(): float
    {
        return $this->gramsConsumed() / 100 * (float) $this->food->sugar_per_100g;
    }

    public function fat(): float
    {
        return $this->gramsConsumed() / 100 * (float) $this->food->fat_per_100g;
    }

    public function protein(): float
    {
        return $this->gramsConsumed() / 100 * (float) $this->food->protein_per_100g;
    }
}
