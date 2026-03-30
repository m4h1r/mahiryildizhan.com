<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'symbol'];

    protected function casts(): array
    {
        return [
            'code' => 'string',
            'name' => 'string',
            'symbol' => 'string',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'currency_id');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'currency_id');
    }
}
