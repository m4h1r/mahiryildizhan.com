<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function people()
    {
        return $this->hasMany(Person::class, 'gender_id');
    }
}
