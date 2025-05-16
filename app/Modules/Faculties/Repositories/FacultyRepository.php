<?php

namespace App\Modules\Faculties\Repositories;

use App\Modules\Faculties\Models\FacultyModel;

class FacultyRepository
{
    public function getAll()
    {
        return FacultyModel::all();
    }

    public function getById($id)
    {
        return FacultyModel::findOrFail($id);
    }

    public function create(array $data)
    {
        return FacultyModel::create($data);
    }

    public function update($id, array $data)
    {
        $faculty = FacultyModel::find($id);
        if ($faculty) {
            $faculty->update($data);
            return $faculty;
        }
        return null;
    }

    public function delete($id)
    {
        $faculty = FacultyModel::find($id);
        if ($faculty) {
            $faculty->delete();
            return true;
        }
        return false;
    }
}