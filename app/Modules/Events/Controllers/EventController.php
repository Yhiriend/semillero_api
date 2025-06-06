<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\StoreEventRequest;
use App\Modules\Events\Requests\ListEventsRequest;
use App\Modules\Events\Requests\UpdateEventRequest;
use App\Modules\Events\Resources\EventResource;
use App\Modules\Events\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Eventos",
 *     description="Endpoints para gestionar eventos"
 * )
 * 
 * @OA\Schema(
 *     schema="EventResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID del evento"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del evento"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, description="Descripción del evento"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date-time", description="Fecha y hora de inicio del evento"),
 *     @OA\Property(property="fecha_fin", type="string", format="date-time", description="Fecha y hora de fin del evento"),
 *     @OA\Property(property="ubicacion", type="string", nullable=true, description="Ubicación del evento"),
 *     @OA\Property(property="coordinador_id", type="integer", description="ID del coordinador del evento"),
 *     @OA\Property(
 *         property="coordinador",
 *         type="object",
 *         description="Información del coordinador",
 *         @OA\Property(property="id", type="integer", description="ID del coordinador"),
 *         @OA\Property(property="nombre", type="string", description="Nombre del coordinador")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="StoreEventRequest",
 *     required={"nombre", "fecha_inicio", "fecha_fin", "coordinador_id"},
 *     @OA\Property(property="nombre", type="string", maxLength=255, description="Nombre del evento"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, description="Descripción del evento"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date-time", description="Fecha y hora de inicio del evento"),
 *     @OA\Property(property="fecha_fin", type="string", format="date-time", description="Fecha y hora de fin del evento"),
 *     @OA\Property(property="ubicacion", type="string", maxLength=255, nullable=true, description="Ubicación del evento"),
 *     @OA\Property(property="coordinador_id", type="integer", description="ID del coordinador del evento")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateEventRequest",
 *     @OA\Property(property="nombre", type="string", maxLength=255, description="Nombre del evento"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, description="Descripción del evento"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date-time", description="Fecha y hora de inicio del evento"),
 *     @OA\Property(property="fecha_fin", type="string", format="date-time", description="Fecha y hora de fin del evento"),
 *     @OA\Property(property="ubicacion", type="string", maxLength=255, nullable=true, description="Ubicación del evento"),
 *     @OA\Property(property="coordinador_id", type="integer", description="ID del coordinador del evento")
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
class EventController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EventService $eventService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/events",
     *     tags={"Eventos"},
     *     summary="Obtener listado de eventos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fecha_inicio",
     *         in="query",
     *         description="Fecha de inicio para filtrar eventos (formato YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="fecha_fin",
     *         in="query",
     *         description="Fecha de fin para filtrar eventos (formato YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="coordinador_nombre",
     *         in="query",
     *         description="Nombre del coordinador para filtrar eventos",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de eventos obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/EventResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron eventos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(ListEventsRequest $request): JsonResponse
    {
        try {
            $events = $this->eventService->getFilteredEvents($request->validated());

            if ($events->isEmpty()) {
                return $this->errorResponse(
                    ResponseCode::NOT_FOUND,
                    404
                );
            }

            return $this->successResponse(
                EventResource::collection($events),
                ResponseCode::SUCCESS
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener el listado de eventos: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     tags={"Eventos"},
     *     summary="Crear un nuevo evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreEventRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evento creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/EventResource"
     *             )
     *         )
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
    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            $event = $this->eventService->createEvent($request->all());
            return $this->successResponse(
                new EventResource($event),
                ResponseCode::SUCCESS,
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al crear el evento: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     tags={"Eventos"},
     *     summary="Obtener detalles de un evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/EventResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $event = $this->eventService->findEvent($id);
            return $this->successResponse(
                new EventResource($event),
                ResponseCode::SUCCESS
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
                'Error al obtener el evento: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     tags={"Eventos"},
     *     summary="Actualizar un evento existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEventRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evento actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/EventResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado",
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
    public function update(UpdateEventRequest $request, $id): JsonResponse
    {
        try {
            $updatedEvent = $this->eventService->updateEvent($id, $request->validated());
            return $this->successResponse(
                new EventResource($updatedEvent),
                ResponseCode::SUCCESS
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
                'Error al actualizar el evento: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     tags={"Eventos"},
     *     summary="Eliminar un evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del evento a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Evento eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=204),
     *             @OA\Property(property="code", type="string", example="SUCCESS"),
     *             @OA\Property(property="message", type="string", example="SUCCESS"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->eventService->deleteEvent($id);
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
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                ResponseCode::VALIDATION_ERROR
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al eliminar el evento: ' . $e->getMessage()
            );
        }
    }

    public function getCoordinators(): JsonResponse
    {
        try {
            $coordinators = $this->eventService->getCoordinators();
            return $this->successResponse(
                $coordinators,
                ResponseCode::SUCCESS
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener los coordinadores: ' . $e->getMessage()
            );
        }
    }

    public function getProjects(Request $request): JsonResponse
    {
        try {
            $projects = $this->eventService->getProjects($request->get('nombre'));
            return $this->successResponse(
                $projects,
                ResponseCode::SUCCESS
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener los proyectos: ' . $e->getMessage()
            );
        }
    }
}