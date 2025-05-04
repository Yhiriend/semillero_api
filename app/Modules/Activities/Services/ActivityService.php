<?php

namespace App\Modules\Activities\Services;

use App\Modules\Activities\Models\ActivityModel;
use App\Modules\Activities\Repositories\ActivityRepository;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    public function __construct(
        protected ActivityRepository $activityRepository
    ) {
    }

    public function getActivitiesByEvent(int $eventId, array $filters)
    {
        return $this->activityRepository->getActivitiesByEvent($eventId, $filters);
    }

    public function findActivity(int $eventId, int $activityId): ActivityModel
    {
        $activity = $this->activityRepository->findOrFail($activityId, $eventId);

        if ($activity->evento_id !== $eventId) {
            throw new \Exception('Actividad no encontrada');
        }

        return $activity;
    }

    public function createActivity(int $eventId, array $data): ActivityModel
    {
        return DB::transaction(function () use ($eventId, $data) {
            $this->validateActivityCreation($eventId, $data);

            $activity = $this->activityRepository->create([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'evento_id' => $eventId,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'estado' => 'pendiente',
            ]);

            if (!empty($data['responsables'])) {
                $this->assignResponsables($activity->id, $eventId, $data['responsables']);
            }

            return $activity->load('responsables');
        });
    }

    protected function validateActivityCreation(int $eventId, array $data): void
    {
        if ($this->activityRepository->existsDuplicate($eventId, $data)) {
            throw new \Exception('Esta actividad ya existe');
        }

        if ($this->activityRepository->existsOverlapping($eventId, $data)) {
            throw new \Exception('La actividad se solapa con otra existente');
        }
    }

    public function updateActivity(int $eventId, int $activityId, array $data): ActivityModel
    {
        return DB::transaction(function () use ($eventId, $activityId, $data) {
            $activity = $this->findActivity($eventId, $activityId);

            if ($activity->fecha_inicio <= now()) {
                throw new \Exception('No se puede modificar una actividad pasada.');
            }

            $this->validateActivityUpdate($activity, $data);

            $this->activityRepository->update($activity, $data);

            if (array_key_exists('responsables', $data)) {
                $this->assignResponsables($activityId, $eventId, $data['responsables']);
            }

            return $activity->fresh()->load('responsables');
        });
    }

    protected function validateActivityUpdate(ActivityModel $activity, array $data): void
    {
        if (
            isset($data['fecha_inicio']) &&
            $this->activityRepository->existsOverlapping(
                $activity->evento_id,
                $data,
                $activity->id
            )
        ) {
            throw new \Exception('El nuevo horario se solapa con otra actividad');
        }
    }

    public function assignResponsables(int $eventId, int $activityId, array $data): ActivityModel
    {
        $activity = $this->findActivity($eventId, $activityId);

        $responsables = is_array($data['responsables']) ? $data['responsables'] : [$data['responsables']];
        $responsables = array_map('intval', $responsables);

        $this->activityRepository->syncResponsables($activityId, $responsables);

        return $activity->fresh()->load('responsables');
    }

    public function deleteActivity(int $eventId, int $activityId): void
    {
        DB::transaction(function () use ($eventId, $activityId) {
            $activity = $this->findActivity($eventId, $activityId);
            $this->activityRepository->delete($activity);
        });
    }
}