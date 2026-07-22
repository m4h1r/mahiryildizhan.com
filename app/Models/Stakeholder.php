<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stakeholder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vkn_tckn',
        'title',
        'name',
        'surname',
        'tax_office_name',
        'city',
        'country',
        'address',
        'phone',
        'email',
        'website',
        'company_type',
        'sector',
        'note',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'stakeholder_id');
    }

    public function incomes()
    {
        return $this->morphMany(Income::class, 'sourceable');
    }
}
