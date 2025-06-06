<?php

namespace App\Modules\Events\Services;

use App\Modules\Events\Repositories\EventRepository;
use App\Modules\Events\Models\EventModel;
use App\Modules\Activities\Models\ActivityModel;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;

class EventService
{

    use ApiResponse;
    
    public function __construct(
        protected EventRepository $eventRepository
    ) {}

    public function getFilteredEvents(array $filters)
    {
        return $this->eventRepository->getFilteredEvents($filters);
    }

    public function findEvent(int $id): EventModel
    {
        return $this->eventRepository->findOrFail($id);
    }

    public function createEvent(array $data): EventModel
    {
        return DB::transaction(function () use ($data) {
            $this->hasConflictCoodinator($data['coordinador_id']);
            $event = $this->eventRepository->create($data);
            
            if (!empty($data['actividades'])) {
                $this->createActivities($event, $data['actividades']);
            }
            
            return $event;
        });
    }

    protected function createActivities(EventModel $event, array $actividades): void
    {
        foreach ($actividades as $actividadData) {
            $actividad = $event->activities()->create([
                'titulo' => $actividadData['titulo'],
                'descripcion' => $actividadData['descripcion'] ?? null,
                'fecha_inicio' => $actividadData['fecha_inicio'],
                'fecha_fin' => $actividadData['fecha_fin'],
                'estado' => 'pendiente'
            ]);

            $this->attachResponsables($actividad, $actividadData['responsables'] ?? []);
        }
    }

    protected function attachResponsables(ActivityModel $actividad, array $responsables): void
    {
        if (!empty($responsables)) {
            $actividad->responsables()->attach($responsables);
        }
    }

    public function updateEvent(int $id, array $data): EventModel
    {
        $event = $this->eventRepository->findOrFail($id);
        $this->hasConflictCoodinator($data['coordinador_id']);
        
        if ($event->fecha_inicio <= now()) {
            throw new \InvalidArgumentException('No se puede editar un evento que ya ha comenzado.');
        }

        return $this->eventRepository->update($event, $data);
    }

    public function deleteEvent(int $id): void
    {
        $event = $this->eventRepository->findOrFail($id);
        
        if ($event->activities()->exists()) {
            throw new \RuntimeException('No se puede eliminar un evento con actividades asociadas.');
        }

        $this->eventRepository->delete($event);
    }

    public function hasConflictCoodinator(int $evaluadorId)
    {
        if(!$this->eventRepository->hasConflictCoodinator($evaluadorId)) {
            throw new \InvalidArgumentException('El coordinador no tiene un rol adecuado para evaluar.');
        }
    }

    public function getProjects($name = null)
    {
        return $this->eventRepository->getProjects($name);
    }

    public function getCoordinators($name = null)
    {
        return $this->eventRepository->getCoordinators( $name);
    }

    public function getResponsables($name = null)
    {
        return $this->eventRepository->getResponsables($name);
    }

}