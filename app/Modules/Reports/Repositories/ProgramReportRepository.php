<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Reports\Models\ProjectReportModel;

class ProgramReportRepository
{
    public function getAll()
    {
        return ProjectReportModel::all();
    }

    public function getById($id)
    {
        return ProjectReportModel::findOrFail($id);
    }

    public function create(array $data)
    {
        return ProjectReportModel::create($data);
    }

    public function update($id, array $data)
    {
        $program = ProjectReportModel::find($id);
        if ($program) {
            $program->update($data);
            return $program;
        }
        return null;
    }

    public function delete($id)
    {
        $program = ProjectReportModel::find($id);
        if ($program) {
            $program->delete();
            return true;
        }
        return false;
    }
}