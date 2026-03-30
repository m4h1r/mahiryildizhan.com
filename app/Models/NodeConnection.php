<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeConnection extends Model
{
    use HasFactory;

    protected $fillable = ['node_from_id', 'node_to_id'];

    public function fromNode()
    {
        return $this->belongsTo(Node::class, 'node_from_id');
    }

    public function toNode()
    {
        return $this->belongsTo(Node::class, 'node_to_id');
    }
}
