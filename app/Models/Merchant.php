<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class Merchant extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password','phone','dob'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class, 'merchant_id', 'id');
    }

    public function ipWhitelists()
    {
        return $this->hasMany(IpWhitelist::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Order::class)->latest();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->latest();
    }

    public function ticketReply()
    {
        return $this->hasManyThrough(TicketReply::class, Ticket::class)->latest();
    }

        /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['role' => 'merchants'];
    }
}
