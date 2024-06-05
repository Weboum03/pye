<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository.
 */
class UserRepository
{
    public function getByUserId(int $userId)
    {
        return User::where('id', $userId)->first();
    }

    public function store(array $data)
    {
        return User::store($data);
    }

    public function getByPhone(string $phone)
    {
        return User::where('phone', $phone)
            ->select('phone', 'email')
            ->first();
    }

    public function getByEmail(string $email)
    {
        return User::where('email', $email)
            ->orWhere('phone', $email)
            ->first();
    }

    public function listing()
    {
        return User::latest()->get();
    }

    public function updatePassword($data)
    {

        return User::where('id', $data['id'])
            ->update(['password' => Hash::make($data['password'])]);
    }

    public function changePassword($data)
    {
        return User::where('id', auth()->user()->id)
            ->update(['password' => Hash::make($data['password'])]);
    }
}
