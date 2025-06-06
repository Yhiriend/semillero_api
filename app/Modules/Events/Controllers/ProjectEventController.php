<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\ProjectEventRequest;
use App\Modules\Events\Resources\ProjectEventResource;
use App\Modules\Events\Services\ProjectEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;

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
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="status", type="integer", example=500),
 *     @OA\Property(property="code", type="string", example="SERVER_ERROR"),
 *     @OA\Property(property="message", type="string", example="SERVER_ERROR"),
 *     @OA\Property(property="errors", type="object", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="status", type="integer", example=422),
 *     @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(property="message", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
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
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProjectEventResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron proyectos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
                    ResponseCode::NOT_FOUND,
                    404
                );
            }
            return $this->successResponse(
                ProjectEventResource::collection($projects)
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener los proyectos: ' . $e->getMessage()
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
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
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
                ResponseCode::SUCCESS,
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al asociar el proyecto: ' . $e->getMessage()
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
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
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
                new ProjectEventResource($relation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener la relación: ' . $e->getMessage()
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
     *             @OA\Property(property="status", type="integer", example=204),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
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
                ResponseCode::SUCCESS,
                204
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al desvincular el proyecto: ' . $e->getMessage()
            );
        }
    }
}