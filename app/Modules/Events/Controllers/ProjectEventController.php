<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\ProjectEventRequest;
use App\Modules\Events\Resources\ProjectEventResource;
use App\Modules\Events\Services\ProjectEventService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class ProjectEventController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProjectEventService $service
    ) {
    }

    public function index(int $eventId): JsonResponse
    {
        $projects = $this->service->getEventProjects($eventId);
        return $this->successResponse(
            ProjectEventResource::collection($projects),
            'Proyectos del evento obtenidos correctamente'
        );
    }

    public function store(ProjectEventRequest $request, int $eventId): JsonResponse
    {
        $relation = $this->service->addProjectToEvent(
            $eventId,
            $request->input('proyecto_id'),
            $request->validated()
        );

        return $this->successResponse(
            new ProjectEventResource($relation),
            'Proyecto asociado al evento exitosamente',
            201
        );
    }

    public function show(int $eventId, int $projectId): JsonResponse
    {
        $relation = $this->service->getProjectEventRelation($eventId, $projectId);
        return $this->successResponse(
            new ProjectEventResource($relation),
            'Relación evento-proyecto obtenida'
        );
    }

    public function destroy(int $eventId, int $projectId): JsonResponse
    {
        $this->service->removeProjectFromEvent($eventId, $projectId);
        return $this->successResponse(
            null,
            'Proyecto desvinculado del evento correctamente',
            204
        );
    }
}