<?php

namespace App\Modules\Events\Services;

use App\Modules\Events\Repositories\ActivityRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityService
{
    public function __construct(private ActivityRepository $activityRepository)
    {
    }

    public function getActivitiesByEvent($filters)
    {
        return $this->activityRepository->getActivitiesByEvent($filters);
    }

    public function getActivityById(int $id, int $eventoId)
    {
        return $this->activityRepository->getActivityById($id, $eventoId);
    }

    public function createActivity(array $data)
    {
        if (!isset($data['evento_id'])) {
            throw new \InvalidArgumentException('El evento_id es requerido para crear una actividad');
        }

        return $this->activityRepository->createActivity($data);
    }

    public function updateActivity(int $id, int $eventoId, array $data)
    {
        return $this->activityRepository->updateActivity($id, $eventoId, $data);
    }

    public function deleteActivity(int $id, int $eventoId)
    {
        return $this->activityRepository->deleteActivity($id, $eventoId);
    }
}