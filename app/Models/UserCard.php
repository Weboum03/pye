<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'number',
        'card_brand'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
