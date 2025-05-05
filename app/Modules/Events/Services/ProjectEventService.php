<?php

namespace App\Modules\Events\Services;

use App\Modules\Events\Repositories\ProjectEventRepository;
use Illuminate\Support\Facades\DB;

class ProjectEventService
{
    public function __construct(
        protected ProjectEventRepository $repository
    ) {
    }

    public function getEventProjects(int $eventId)
    {
        return $this->repository->getByEvent($eventId);
    }

    public function addProjectToEvent(int $eventId, int $projectId, array $data)
    {
        return DB::transaction(function () use ($eventId, $projectId, $data) {
            if ($this->repository->relationExists($eventId, $projectId)) {
                throw new \Exception('El proyecto ya está asociado a este evento');
            }

            return $this->repository->createRelation([
                'evento_id' => $eventId,
                'proyecto_id' => $projectId,
                'fecha_inscripcion' => $data['fecha_inscripcion'],
                'observaciones' => $data['observaciones'] ?? null
            ]);
        });
    }

    public function removeProjectFromEvent(int $eventId, int $projectId): bool
    {
        return $this->repository->deleteRelation($eventId, $projectId);
    }

    public function getProjectEventRelation(int $eventId, int $projectId)
    {
        return $this->repository->findRelation($eventId, $projectId);
    }
}