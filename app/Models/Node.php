<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'text_color', 'text_size', 'image'];

    public function linksFrom()
    {
        return $this->hasMany(NodeConnection::class, 'node_from_id');
    }

    public function linksTo()
    {
        return $this->hasMany(NodeConnection::class, 'node_to_id');
    }
}
