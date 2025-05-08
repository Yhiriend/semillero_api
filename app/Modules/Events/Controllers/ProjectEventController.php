<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\ProjectEventRequest;
use App\Modules\Events\Resources\ProjectEventResource;
use App\Modules\Events\Services\ProjectEventService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

/**
 * @OA\Schema(
 *     schema="ProjectEventResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="event_id", type="integer"),
 *     @OA\Property(property="proyecto_id", type="integer"),
 *     @OA\Property(
 *         property="proyecto",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="nombre", type="string")
 *     ),
 *     @OA\Property(
 *         property="evento",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="nombre", type="string")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="ProjectEventRequest",
 *     required={"proyecto_id"},
 *     @OA\Property(property="proyecto_id", type="integer")
 * )
 */
class ProjectEventController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProjectEventService $service
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}/projects",
     *     tags={"Event Projects"},
     *     summary="Get all projects for an event",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of event projects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyectos del evento obtenidos correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProjectEventResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(int $eventId): JsonResponse
    {
        $projects = $this->service->getEventProjects($eventId);
        return $this->successResponse(
            ProjectEventResource::collection($projects),
            'Proyectos del evento obtenidos correctamente'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/events/{event}/projects",
     *     tags={"Event Projects"},
     *     summary="Add a project to an event",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProjectEventRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project added to event successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto asociado al evento exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProjectEventResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/events/{event}/projects/{project}",
     *     tags={"Event Projects"},
     *     summary="Get project-event relationship details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project-event relationship details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Relación evento-proyecto obtenida"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProjectEventResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relationship not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $eventId, int $projectId): JsonResponse
    {
        $relation = $this->service->getProjectEventRelation($eventId, $projectId);
        return $this->successResponse(
            new ProjectEventResource($relation),
            'Relación evento-proyecto obtenida'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{event}/projects/{project}",
     *     tags={"Event Projects"},
     *     summary="Remove a project from an event",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Project removed from event successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto desvinculado del evento correctamente"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relationship not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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