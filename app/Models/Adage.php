<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adage extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = ['owner', 'adage', 'keywords', 'language_id', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function language()
    {
        return $this->belongsTo(PostLanguage::class, 'language_id');
    }
}
