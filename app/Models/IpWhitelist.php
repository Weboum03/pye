<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $fillable = ['merchant_id', 'ip_address', 'domain', 'status'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
