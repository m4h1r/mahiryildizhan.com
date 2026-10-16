<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['slug', 'file_path', 'original_name', 'mime_type', 'size', 'expires_at', 'download_count'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'size' => 'integer',
            'download_count' => 'integer',
        ];
    }
}
