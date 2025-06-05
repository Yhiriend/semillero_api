<?php

namespace App\Modules\Reports\Controllers;

use App\Modules\Reports\Services\EvaluatorService;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Http\JsonResponse;

class EvaluatorController
{
    use ApiResponse;

    protected EvaluatorService $evaluatorService;

    public function __construct(EvaluatorService $evaluatorService)
    {
        $this->evaluatorService = $evaluatorService;
    }

    /**
     * Get all evaluators with their assigned projects
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $evaluators = $this->evaluatorService->getEvaluatorsWithProjects();
            
            return $this->successResponse($evaluators, ResponseCode::DATA_LOADED);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::SERVER_ERROR, 500);
        }
    }
} 