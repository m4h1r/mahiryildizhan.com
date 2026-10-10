<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = [
        'date',
        'stakeholder_id',
        'expense_type_id',
        'currency_id',
        'description',
        'price',
        'quantity',
        'tax',
        'total',
        'company_expense',
        'paid_by_others',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'company_expense' => 'boolean',
            'paid_by_others' => 'boolean',
            'price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stakeholder()
    {
        return $this->belongsTo(Stakeholder::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
