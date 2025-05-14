<?php

namespace App\Modules\Activities\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Activities\Requests\AssignResponsablesRequest;
use App\Modules\Activities\Requests\StoreActivityRequest;
use App\Modules\Activities\Requests\UpdateActivityRequest;
use App\Modules\Activities\Resources\ActivityResource;
use App\Modules\Activities\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ActivityController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function index(Request $request, int $eventId): JsonResponse
    {
        $activities = $this->activityService->getActivitiesByEvent($eventId, $request->all());
        return $this->successResponse(
            ActivityResource::collection($activities),
            'Listado de actividades obtenido correctamente'
        );
    }

    public function store(StoreActivityRequest $request, int $eventId): JsonResponse
    {
        try {
            $activity = $this->activityService->createActivity($eventId, $request->validated());
            return $this->successResponse(
                new ActivityResource($activity),
                'Actividad creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function show(int $eventId, int $activityId): JsonResponse
    {
        try {
            $activity = $this->activityService->findActivity($eventId, $activityId);
            return $this->successResponse(
                new ActivityResource($activity),
                'Actividad encontrada'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Actividad no encontrada', 404);
        }
    }

    public function update(UpdateActivityRequest $request, int $eventId, int $activityId): JsonResponse
    {
        try {
            $activity = $this->activityService->updateActivity($eventId, $activityId, $request->validated());
            return $this->successResponse(
                new ActivityResource($activity),
                'Actividad actualizada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function assignResponsables(AssignResponsablesRequest $request, int $eventId, int $activityId): JsonResponse
    {
        try {
            $activity = $this->activityService->assignResponsables($eventId, $activityId, $request->validated());
            return $this->successResponse(
                new ActivityResource($activity),
                'Responsables asignados correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroy(int $eventId, int $activityId): JsonResponse
    {
        try {
            $this->activityService->deleteActivity($eventId, $activityId);
            return $this->successResponse(
                null,
                'Actividad eliminada correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}