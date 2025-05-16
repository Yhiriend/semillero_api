<?php

namespace App\Modules\Reports\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


use App\Http\Controllers\Controller;
use App\Modules\Reports\Models\Proyecto;
use App\Modules\Reports\Services\EventService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventosController extends Controller
{
    use ApiResponse;

    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Get all projects with their authors
     *
     * @return JsonResponse
     */
    public function getProjectsWithAuthors(): JsonResponse
    {
        $projects = Proyecto::with([
            'autores:id,nombre,email,tipo',
            'lider:id,nombre,email,tipo',
            'coordinador:id,nombre,email,tipo'
        ])->get();

        return $this->successResponse($projects, 'Lista de proyectos con sus autores obtenida exitosamente');
    }

    public function getRegisteredUsers(int $eventId): JsonResponse
    {
        try {
            return $this->eventService->getRegisteredUsers($eventId);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los usuarios inscritos: ' . $e->getMessage());
        }
    }
}