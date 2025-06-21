<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Models\Eventos;
use App\Modules\Reports\Models\EventReportModel;
use App\Traits\ApiResponse;

class EventService
{

    use ApiResponse;
    
    public function __construct(
    ) {}

public function getEnrolledStudents(int $eventId)
    {
        try {
            $event = EventReportModel::findOrFail($eventId);
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