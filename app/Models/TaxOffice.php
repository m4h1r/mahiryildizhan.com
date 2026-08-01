<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxOffice extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function stakeholders()
    {
        return $this->hasMany(Stakeholder::class, 'tax_office_id');
    }
}
