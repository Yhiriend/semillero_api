<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Universities\Services\UniversityService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Enums\ResponseCode;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Universidades",
 *     description="Operaciones relacionadas con universidades"
 * )
 */
class UniversityReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected UniversityService $universityService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/universities",
     *     summary="Obtener todas las universidades",
     *     description="Devuelve una lista de todas las universidades registradas.",
     *     operationId="getAllUniversities",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de universidades obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Universidad Ejemplo"),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z")
     *             )),
     *             @OA\Property(property="message", type="string", example="Universidades obtenidas correctamente"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontraron universidades inscritas"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $universities = $this->universityService->getAll();
    
            return $this->successResponse([
                'mensaje'      => 'Universidades obtenidas correctamente',
                'total'        => count($universities),
                'universidades'=> $universities
            ], ResponseCode::SUCCESS, 200);
    
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al consultar las universidades: ' . $e->getMessage());
        }
    }
}