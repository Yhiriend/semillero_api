<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Events\Models\EventModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class EventRepository
{
    public function getFilteredEvents(array $filters): Collection
    {
        return $this->buildQuery($filters)
            ->with(['coordinador', 'activities.responsables'])
            ->get();
    }

    public function findOrFail(int $id): EventModel
    {
        $event = EventModel::with(['coordinador', 'activities.responsables'])->find($id);

        if (!$event) {
            throw new ModelNotFoundException("Evento no encontrado con ID: {$id}");
        }

        return $event;
    }

    public function create(array $data): EventModel
    {
        return EventModel::create($data);
    }

    public function update(EventModel $event, array $data): EventModel
    {
        $event->update($data);
        return $event->fresh();
    }

    public function delete(EventModel $event): void
    {
        $event->delete();
    }

    protected function buildQuery(array $filters): Builder
    {
        $query = EventModel::with(['coordinador', 'activities.responsables']);

        if (isset($filters['fecha_inicio'])) {
            $query->where('fecha_inicio', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin'])) {
            $query->where('fecha_fin', '<=', $filters['fecha_fin']);
        }

        if (isset($filters['coordinador_id'])) {
            $query->where('coordinador_id', $filters['coordinador_id']);
        }

        return $query;
    }
    public function hasConflictCoodinator(int $evaluadorId)
    {
        return DB::table('Usuario')
            ->join('Usuario_Rol', 'Usuario.id', '=', 'Usuario_Rol.usuario_id')
            ->where('Usuario.id', $evaluadorId)
            ->where('Usuario.tipo', 'profesor')
            ->where('Usuario_Rol.rol_id', 5)
            ->exists();
    }
}