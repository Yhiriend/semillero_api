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

    public function getRegisteredUsers(int $eventId)
    {
        try {
            $event = EventModel::findOrFail($eventId);
            $users = $event->registeredUsers()->get();

            $groupedData = $users->groupBy('semillero_id')->map(function ($group) {
                $firstUser = $group->first();
                return [
                    'semillero' => [
                        'id' => $firstUser->semillero_id,
                        'nombre' => $firstUser->semillero_nombre
                    ],
                    'proyectos' => $group->groupBy('proyecto_id')->map(function ($projectGroup) {
                        $firstProject = $projectGroup->first();
                        return [
                            'id' => $firstProject->proyecto_id,
                            'titulo' => $firstProject->proyecto_titulo,
                            'fecha_inscripcion' => $firstProject->fecha_inscripcion,
                            'usuarios' => $projectGroup->map(function ($user) {
                                return [
                                    'id' => $user->id,
                                    'nombre' => $user->nombre,
                                    'email' => $user->email,
                                    'tipo' => $user->tipo
                                ];
                            })->values()
                        ];
                    })->values()
                ];
            })->values();

            return $this->successResponse($groupedData, 'Usuarios registrados obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los usuarios registrados: ' . $e->getMessage());
        }
    }
}