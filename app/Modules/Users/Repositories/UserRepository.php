<?php

namespace App\Modules\Users\Repositories;

use App\Modules\Users\Models\UserModel;

class UserRepository
{
    public function all()
    {
        return UserModel::all();
    }

    public function find($id)
    {
        return UserModel::findOrFail($id);
    }

    public function create(array $data)
    {
        return UserModel::create($data);
    }

    public function update($id, array $data)
    {
        $user = UserModel::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = UserModel::findOrFail($id);
        $user->delete();
        return true;
    }
} 