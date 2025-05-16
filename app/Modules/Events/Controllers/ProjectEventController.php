<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\ProjectEventRequest;
use App\Modules\Events\Resources\ProjectEventResource;
use App\Modules\Events\Services\ProjectEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Proyectos de Eventos",
 *     description="Endpoints para gestionar la relación entre eventos y proyectos"
 * )
 * 
 * @OA\Schema(
 *     schema="ProjectEventResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID de la relación evento-proyecto"),
 *     @OA\Property(property="evento_id", type="integer", description="ID del evento"),
 *     @OA\Property(property="proyecto_id", type="integer", description="ID del proyecto"),
 *     @OA\Property(property="fecha_inscripcion", type="string", format="date-time", description="Fecha de inscripción del proyecto al evento"),
 *     @OA\Property(property="observaciones", type="string", nullable=true, description="Observaciones sobre la inscripción"),
 *     @OA\Property(
 *         property="proyecto",
 *         type="object",
 *         description="Información del proyecto",
 *         @OA\Property(property="id", type="integer", description="ID del proyecto"),
 *         @OA\Property(property="nombre", type="string", description="Nombre del proyecto")
 *     ),
 *     @OA\Property(
 *         property="evento",
 *         type="object",
 *         description="Información del evento",
 *         @OA\Property(property="id", type="integer", description="ID del evento"),
 *         @OA\Property(property="nombre", type="string", description="Nombre del evento")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
 * 
 * @OA\Schema(
 *     schema="ProjectEventRequest",
 *     required={"proyecto_id", "fecha_inscripcion"},
 *     @OA\Property(property="proyecto_id", type="integer", description="ID del proyecto a asociar"),
 *     @OA\Property(property="fecha_inscripcion", type="string", format="date-time", description="Fecha de inscripción del proyecto"),
 *     @OA\Property(property="observaciones", type="string", maxLength=500, nullable=true, description="Observaciones sobre la inscripción")
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
     *     tags={"Proyectos de Eventos"},
     *     summary="Obtener todos los proyectos asociados a un evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proyectos obtenida correctamente",
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
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(int $eventId): JsonResponse
    {
        try {
            $projects = $this->service->getEventProjects($eventId);

            if ($projects->isEmpty()) {
                return $this->errorResponse(
                    'No se encontraron proyectos asociados a este evento',
                    404
                );
            }
            return $this->successResponse(
                ProjectEventResource::collection($projects),
                'Proyectos del evento obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los proyectos: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/events/{event}/projects",
     *     tags={"Proyectos de Eventos"},
     *     summary="Asociar un proyecto a un evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProjectEventRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proyecto asociado al evento exitosamente",
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
     *         response=404,
     *         description="Evento o proyecto no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(ProjectEventRequest $request, int $eventId): JsonResponse
    {
        try {
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
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evento o proyecto no encontrado',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al asociar el proyecto: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}/projects/{project}",
     *     tags={"Proyectos de Eventos"},
     *     summary="Obtener detalles de la relación evento-proyecto",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID del proyecto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la relación obtenidos correctamente",
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
     *         description="Relación no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(int $eventId, int $projectId): JsonResponse
    {
        try {
            $relation = $this->service->getProjectEventRelation($eventId, $projectId);
            return $this->successResponse(
                new ProjectEventResource($relation),
                'Relación evento-proyecto obtenida'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Relación no encontrada',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener la relación: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{event}/projects/{project}",
     *     tags={"Proyectos de Eventos"},
     *     summary="Desvincular un proyecto de un evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         description="ID del proyecto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Proyecto desvinculado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proyecto desvinculado del evento correctamente"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(int $eventId, int $projectId): JsonResponse
    {
        try {
            $deleted = $this->service->removeProjectFromEvent($eventId, $projectId);
            if (!$deleted) {
                throw new ModelNotFoundException('Relación no encontrada');
            }
            return $this->successResponse(
                null,
                'Proyecto desvinculado del evento correctamente',
                204
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Relación no encontrada',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al desvincular el proyecto: ' . $e->getMessage(),
                500
            );
        }
    }
}