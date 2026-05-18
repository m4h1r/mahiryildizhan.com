<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'status', 'subscribed_at', 'unsubscribed_at', 'mailchimp_id', 'confirmation_token', 'confirmed_at'];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }
}
