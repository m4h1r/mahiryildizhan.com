<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeSource extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function incomes()
    {
        return $this->hasMany(Income::class, 'income_source_id');
    }
}
