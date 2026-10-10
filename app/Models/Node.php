<?php

namespace App\Models;

use App\Traits\TracksCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksCreatedBy;

    protected $fillable = ['name', 'text_color', 'text_size', 'image', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function linksFrom()
    {
        return $this->hasMany(NodeConnection::class, 'node_from_id');
    }

    public function linksTo()
    {
        return $this->hasMany(NodeConnection::class, 'node_to_id');
    }
}
