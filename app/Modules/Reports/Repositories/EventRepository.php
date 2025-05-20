<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Reports\Models\Eventos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventRepository
{
    public function getFilteredEvents(array $filters): Collection
    {
        return $this->buildQuery($filters)->get();
    }

    public function findOrFail(int $id): Eventos
    {
        $event = Eventos::with('coordinador')->find($id);

        if (!$event) {
            throw new ModelNotFoundException("Evento no encontrado con ID: {$id}");
        }

        return $event;
    }

    public function create(array $data): Eventos
    {
        return Eventos::create($data);
    }

    public function update(Eventos $event, array $data): Eventos
    {
        $event->update($data);
        return $event->fresh();
    }

    public function delete(Eventos $event): void
    {
        $event->delete();
    }

    protected function buildQuery(array $filters): Builder
    {
        $query = Eventos::with('coordinador');

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
}