<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'government_acceptance_percentage'];

    protected function casts(): array
    {
        return [
            'government_acceptance_percentage' => 'integer',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_type_id');
    }
}
