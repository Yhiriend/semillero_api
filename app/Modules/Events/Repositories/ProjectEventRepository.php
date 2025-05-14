<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Events\Models\ProjectEvent;
use App\Modules\Events\Models\ProjectEventModel;

class ProjectEventRepository
{
    public function getByEvent(int $eventId)
    {
        return ProjectEventModel::where('evento_id', $eventId)->get();
    }

    public function findRelation(int $eventId, int $projectId)
    {
        return ProjectEventModel::where('evento_id', $eventId)
                          ->where('proyecto_id', $projectId)
                          ->firstOrFail();
    }

    public function relationExists(int $eventId, int $projectId): bool
    {
        return ProjectEventModel::where('evento_id', $eventId)
                          ->where('proyecto_id', $projectId)
                          ->exists();
    }

    public function createRelation(array $data)
    {
        return ProjectEventModel::create($data);
    }

    public function deleteRelation(int $eventId, int $projectId): bool
    {
        return ProjectEventModel::where('evento_id', $eventId)
                         ->where('proyecto_id', $projectId)
                         ->delete() > 0;
    }
}