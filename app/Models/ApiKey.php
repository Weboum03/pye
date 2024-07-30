<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = ['merchant_id', 'key'];

    public static function generate()
    {
        do {
            $key = Str::random(40);
        } while (self::where('key', $key)->exists());

        return $key;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
