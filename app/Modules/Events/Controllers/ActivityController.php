<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Services\ActivityService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityController extends Controller
{
    use ApiResponse;

    public function __construct(private ActivityService $activityService)
    {
    }

    public function index(Request $request, int $eventoId)
    {
        try {
            $filters = array_merge($request->all(), ['evento_id' => $eventoId]);
            $activities = $this->activityService->getActivitiesByEvent($filters);
            return $this->successResponse(
                $activities,
                ResponseCode::SUCCESS
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener las actividades: ' . $e->getMessage()
            );
        }
    }

    public function show(int $eventoId, int $id)
    {
        try {
            $activity = $this->activityService->getActivityById($id, $eventoId);
            return $this->successResponse(
                $activity,
                ResponseCode::SUCCESS
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404,
                'Actividad no encontrada'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al obtener la actividad: ' . $e->getMessage()
            );
        }
    }

    public function store(Request $request, int $eventoId)
    {
        try {
            $data = array_merge($request->all(), ['evento_id' => $eventoId]);
            $activity = $this->activityService->createActivity($data);
            return $this->successResponse(
                $activity,
                ResponseCode::SUCCESS
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse(
                ResponseCode::VALIDATION_ERROR,
                400,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al crear la actividad: ' . $e->getMessage()
            );
        }
    }

    public function update(int $eventoId, int $id, Request $request)
    {
        try {
            $data = $request->all();
            $activity = $this->activityService->updateActivity($id, $eventoId, $data);
            return $this->successResponse(
                $activity,
                ResponseCode::SUCCESS
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404,
                'Actividad no encontrada'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al actualizar la actividad: ' . $e->getMessage()
            );
        }
    }

    public function destroy(int $eventoId, int $id)
    {   
        try {
            $this->activityService->deleteActivity($id, $eventoId);
            return $this->successResponse(
                null,
                ResponseCode::SUCCESS
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                404,
                'Actividad no encontrada'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::SERVER_ERROR,
                500,
                'Error al eliminar la actividad: ' . $e->getMessage()
            );
        }
    }
}