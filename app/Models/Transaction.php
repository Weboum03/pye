<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'order_id', 'amount', 'type', 'status', 'created_at', 'updated_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
