<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'interaction_type_id');
    }
}
