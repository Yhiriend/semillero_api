<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Events\Models\ActivityModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityRepository
{
    public function getActivitiesByEvent($filters)
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;

        $query = ActivityModel::query()
            ->with(['responsables:id,nombre,email,tipo'])
            ->where('evento_id', $filters['evento_id']);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getActivityById(int $id, int $eventoId)
    {
        $activity = ActivityModel::with(['responsables:id,nombre,email,tipo'])
            ->where('id', $id)
            ->where('evento_id', $eventoId)
            ->first();

        if (!$activity) {
            throw new ModelNotFoundException("Actividad no encontrada con ID: {$id} para el evento: {$eventoId}");
        }

        return $activity;
    }

    public function createActivity(array $data)
    {
        $activity = ActivityModel::create($data);
        
        if (isset($data['responsables'])) {
            $activity->responsables()->sync($data['responsables']);
        }

        return $activity->load('responsables:id,nombre,email,tipo');
    }

    public function updateActivity(int $id, int $eventoId, array $data)
    {
        $activity = $this->getActivityById($id, $eventoId);
        
        $activity->update($data);

        if (isset($data['responsables'])) {
            $activity->responsables()->sync($data['responsables']);
        }

        return $activity->fresh(['responsables:id,nombre,email,tipo']);
    }

    public function deleteActivity(int $id, int $eventoId)
    {
        $activity = $this->getActivityById($id, $eventoId);
        $activity->responsables()->detach();
        $activity->delete();
    }
}