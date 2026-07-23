<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    use HasFactory;

    // Laravel's pluralizer treats "Food" as uncountable and would guess the
    // table name "food" (singular) — same class of bug as Media/"medium".
    protected $table = 'foods';

    public const VITAMINS = [
        'vitamin_a' => 'Vitamin A (mcg)',
        'vitamin_b1' => 'B1 - Tiamin (mg)',
        'vitamin_b2' => 'B2 - Riboflavin (mg)',
        'vitamin_b6' => 'B6 (mg)',
        'vitamin_b12' => 'B12 (mcg)',
        'vitamin_c' => 'Vitamin C (mg)',
        'vitamin_d' => 'Vitamin D (mcg)',
        'vitamin_e' => 'Vitamin E (mg)',
        'vitamin_k' => 'Vitamin K (mcg)',
        'folat' => 'Folat - B9 (mcg)',
        'calcium' => 'Kalsiyum (mg)',
        'iron' => 'Demir (mg)',
        'magnesium' => 'Magnezyum (mg)',
        'potassium' => 'Potasyum (mg)',
        'zinc' => 'Çinko (mg)',
    ];

    protected $fillable = [
        'name',
        'calories_per_100g',
        'carbs_per_100g',
        'sugar_per_100g',
        'fat_per_100g',
        'unit_type',
        'grams_per_unit',
        'vitamins',
    ];

    protected function casts(): array
    {
        return [
            'calories_per_100g' => 'integer',
            'carbs_per_100g' => 'decimal:2',
            'sugar_per_100g' => 'decimal:2',
            'fat_per_100g' => 'decimal:2',
            'grams_per_unit' => 'decimal:2',
            'vitamins' => 'array',
        ];
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class);
    }
}
