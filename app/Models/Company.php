<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['merchant_id', 'name', 'email', 'phone', 'address'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
