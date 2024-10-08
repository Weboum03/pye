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
        'exp_month',
        'exp_year',
        'card_brand',
        'default',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
