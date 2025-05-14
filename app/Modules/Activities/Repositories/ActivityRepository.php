<?php

namespace App\Modules\Activities\Repositories;

use App\Modules\Activities\Models\ActivityModel;
use Illuminate\Support\Facades\DB;


class ActivityRepository
{
    public function getActivitiesByEvent(int $eventId, array $filters)
    {
        $query = ActivityModel::where('evento_id', $eventId);

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        return $query->with('responsables')->get();
    }

    public function findOrFail(int $id, ?int $eventId = null): ActivityModel
    {
        $query = ActivityModel::with('responsables');

        if ($eventId !== null) {
            $query->where('evento_id', $eventId);
        }

        return $query->findOrFail($id);
    }

    public function existsDuplicate(int $eventId, array $data): bool
    {
        return ActivityModel::where('evento_id', $eventId)
            ->where('titulo', $data['titulo'])
            ->where('fecha_inicio', $data['fecha_inicio'])
            ->where('fecha_fin', $data['fecha_fin'])
            ->exists();
    }

    public function existsOverlapping(int $eventId, array $data, ?int $excludeId = null): bool
    {
        $query = ActivityModel::where('evento_id', $eventId)
            ->where(function ($query) use ($data) {
                $query->where('fecha_inicio', '<', $data['fecha_fin'])
                      ->where('fecha_fin', '>', $data['fecha_inicio']);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(array $data): ActivityModel
    {
        return ActivityModel::create($data);
    }

    public function update(ActivityModel $activity, array $data): void
    {
        $activity->update($data);
    }

    public function syncResponsables(int $activityId, array $responsables): void
    {
        DB::table('Actividad_Responsable')
            ->where('actividad_id', $activityId)
            ->delete();

        if (!empty($responsables)) {
            $insertData = array_map(function ($id) use ($activityId) {
                return [
                    'actividad_id' => $activityId,
                    'responsable_id' => $id
                ];
            }, $responsables);

            DB::table('Actividad_Responsable')->insert($insertData);
        }
    }

    public function delete(ActivityModel $activity): void
    {
        $activity->responsables()->detach();
        $activity->delete();
    }
}