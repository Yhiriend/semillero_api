<?php

namespace App\Modules\Reports\Controllers;

use App\Modules\Reports\Services\EventInscriptionService;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;

class EventInscriptionController
{
    use ApiResponse;

    protected EventInscriptionService $eventInscriptionService;

    public function __construct(EventInscriptionService $eventInscriptionService)
    {
        $this->eventInscriptionService = $eventInscriptionService;
    }

    /**
     * Get all students enrolled in events
     *
     * @return JsonResponse
     */
    public function getEnrolledStudents(): JsonResponse
    {
        try {
            $students = $this->eventInscriptionService->getEnrolledStudents();
            
            if ($students->isEmpty()) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 404);
            }

            return $this->successResponse($students, ResponseCode::DATA_LOADED);
        } catch (QueryException $e) {
            return $this->errorResponse(ResponseCode::SERVER_ERROR, 500, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::SERVER_ERROR, 500);
        }
    }
} 