<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adage extends Model
{
    use HasFactory;

    protected $fillable = ['owner', 'adage', 'keywords', 'language_id'];

    public function language()
    {
        return $this->belongsTo(PostLanguage::class, 'language_id');
    }
}
