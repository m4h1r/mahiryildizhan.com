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
        'tax_office_id',
        'sector_id',
        'title',
        'name',
        'surname',
        'city',
        'country',
        'address',
        'phone',
        'email',
        'website',
        'company_type',
        'note',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
            'tax_office_id' => 'integer',
            'sector_id' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function taxOffice()
    {
        return $this->belongsTo(TaxOffice::class, 'tax_office_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
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
