<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Requests\StoreEventRequest;
use App\Modules\Events\Requests\ListEventsRequest;
use App\Modules\Events\Requests\UpdateEventRequest;
use App\Modules\Events\Resources\EventResource;
use App\Modules\Events\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponse;

class EventController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EventService $eventService
    ) {
    }

    public function index(ListEventsRequest $request): JsonResponse
    {
        try {
            $events = $this->eventService->getFilteredEvents($request->validated());
            return $this->successResponse(
                EventResource::collection($events),
                'Listado de eventos obtenido correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener el listado de eventos: ' . $e->getMessage(),
                500
            );
        }
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            $event = $this->eventService->createEvent($request->validated());
            return $this->successResponse(
                new EventResource($event),
                'Evento creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear el evento: ' . $e->getMessage(),
                500
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $event = $this->eventService->findEvent($id);
            return $this->successResponse(
                new EventResource($event),
                'Evento encontrado'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evento no encontrado',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener el evento: ' . $e->getMessage(),
                500
            );
        }
    }

    public function update(UpdateEventRequest $request, $id): JsonResponse
    {
        try {
            $updatedEvent = $this->eventService->updateEvent($id, $request->validated());
            return $this->successResponse(
                new EventResource($updatedEvent),
                'Evento actualizado correctamente'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evento no encontrado',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar el evento: ' . $e->getMessage(),
                500
            );
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->eventService->deleteEvent($id);
            return $this->successResponse(
                null,
                'Evento eliminado correctamente',
                204
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evento no encontrado',
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar el evento: ' . $e->getMessage(),
                500
            );
        }
    }
}