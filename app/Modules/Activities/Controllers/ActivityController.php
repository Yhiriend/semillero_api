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

/**
 * @OA\Tag(
 *     name="Activities",
 *     description="Endpoints for managing event activities"
 * )
 * 
 * @OA\Schema(
 *     schema="ActivityResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="start_date", type="string", format="date-time"),
 *     @OA\Property(property="end_date", type="string", format="date-time"),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="event_id", type="integer"),
 *     @OA\Property(
 *         property="responsables",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="StoreActivityRequest",
 *     required={"name", "start_date", "end_date"},
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date-time"),
 *     @OA\Property(property="end_date", type="string", format="date-time"),
 *     @OA\Property(property="location", type="string", maxLength=255, nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateActivityRequest",
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date-time"),
 *     @OA\Property(property="end_date", type="string", format="date-time"),
 *     @OA\Property(property="location", type="string", maxLength=255, nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="AssignResponsablesRequest",
 *     required={"responsables"},
 *     @OA\Property(
 *         property="responsables",
 *         type="array",
 *         @OA\Items(type="integer")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="status", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error message")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="status", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation Error"),
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
class ActivityController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ActivityService $activityService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/events/{eventId}/activities",
     *     tags={"Activities"},
     *     summary="Get all activities for an event",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of activities",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Listado de actividades obtenido correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ActivityResource")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request, int $eventId): JsonResponse
    {
        $activities = $this->activityService->getActivitiesByEvent($eventId, $request->all());
        return $this->successResponse(
            ActivityResource::collection($activities),
            'Listado de actividades obtenido correctamente'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/events/{eventId}/activities",
     *     tags={"Activities"},
     *     summary="Create a new activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreActivityRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Activity created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Actividad creada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ActivityResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/events/{eventId}/activities/{activityId}",
     *     tags={"Activities"},
     *     summary="Get activity details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="activityId",
     *         in="path",
     *         description="ID of the activity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Actividad encontrada"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ActivityResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/events/{eventId}/activities/{activityId}",
     *     tags={"Activities"},
     *     summary="Update an activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="activityId",
     *         in="path",
     *         description="ID of the activity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateActivityRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Actividad actualizada correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ActivityResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/events/{eventId}/activities/{activityId}/assign-responsables",
     *     tags={"Activities"},
     *     summary="Assign responsables to activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="activityId",
     *         in="path",
     *         description="ID of the activity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AssignResponsablesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Responsables assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Responsables asignados correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ActivityResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/events/{eventId}/activities/{activityId}",
     *     tags={"Activities"},
     *     summary="Delete an activity",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="activityId",
     *         in="path",
     *         description="ID of the activity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Actividad eliminada correctamente"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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