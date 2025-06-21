<?php

namespace App\Modules\Reports\Controllers;

use App\Modules\Reports\Services\EvaluatorService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Enums\ResponseCode;

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
        $evaluators = $this->evaluatorService->getEvaluatorsWithProjects();
    
        return $this->successResponse([
            'mensaje'     => 'Evaluadores obtenidos exitosamente',
            'evaluadores' => $evaluators,
        ], ResponseCode::SUCCESS, 200);
    }
    
} 