<?php

namespace App\Modules\Universities\Repositories;

use App\Modules\Universities\Models\UniversityModel;

class UniversityRepository
{
    public function getAll()
    {
        return UniversityModel::all();
    }

    public function getById($id)
    {
        return UniversityModel::findOrFail($id);
    }

    public function create(array $data)
    {
        return UniversityModel::create($data);
    }

    public function update($id, array $data)
    {
        $university = UniversityModel::find($id);
        if ($university) {
            $university->update($data);
            return $university;
        }
        return null;
    }

    public function delete($id)
    {
        $university = UniversityModel::find($id);
        if ($university) {
            $university->delete();
            return true;
        }
        return false;
    }
}