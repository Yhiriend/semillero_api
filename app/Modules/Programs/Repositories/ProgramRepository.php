<?php

namespace App\Modules\Programs\Repositories;

use App\Modules\Programs\Models\ProgramModel;

class ProgramRepository
{
    public function getAll()
    {
        return ProgramModel::all();
    }

    public function getById($id)
    {
        return ProgramModel::findOrFail($id);
    }

    public function create(array $data)
    {
        return ProgramModel::create($data);
    }

    public function update($id, array $data)
    {
        $program = ProgramModel::find($id);
        if ($program) {
            $program->update($data);
            return $program;
        }
        return null;
    }

    public function delete($id)
    {
        $program = ProgramModel::find($id);
        if ($program) {
            $program->delete();
            return true;
        }
        return false;
    }
}