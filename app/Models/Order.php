<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'merchant_id',
        'user_id',
        'first_name',
        'last_name',
        'address',
        'postal_code',
        'type',
        'card_number',
        'exp_month',
        'exp_year',
        'cvc',
        'tax',
        'total_amount',
        'status'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class);
    }
}
