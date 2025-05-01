<?php

namespace App\Modules\Authentication\Repositories;

use App\Modules\Users\Models\UserModel;

class AuthRepository
{
    public function createUser(array $data): UserModel
    {
        return UserModel::create($data);
    }

    public function findByEmail(string $email): ?UserModel
    {
        return UserModel::where('email', $email)->first();
    }
}
