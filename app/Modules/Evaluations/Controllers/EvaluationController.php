<?php

namespace App\Modules\Evaluations\Controllers;

use App\Modules\Evaluations\Requests\CompleteEvaluationRequest;
use App\Modules\Evaluations\Requests\MassAssignRequest;
use App\Modules\Evaluations\Requests\ReassignEvaluatorRequest;
use App\Modules\Evaluations\Requests\StoreEvaluationRequest;
use App\Modules\Evaluations\Requests\UpdateEvaluationRequest;
use App\Modules\Evaluations\Services\EvaluationService;
use App\Modules\Projects\Models\Project;
use App\Modules\Users\Models\UserModel;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Evaluaciones",
 *     description="Operaciones relacionadas con las evaluaciones"
 * )
 */
class EvaluationController extends Controller
{
    use ApiResponse;

    public function __construct(protected EvaluationService $evaluationService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations",
     *     tags={"Evaluaciones"},
     *     summary="Obtener todas las evaluaciones",
     *     description="Devuelve una lista paginada de evaluaciones con filtros opcionales",
     *     operationId="getAllEvaluations",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="query",
     *         description="Filtrar por título del proyecto",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="evaluator",
     *         in="query",
     *         description="Filtrar por nombre del evaluador",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pendiente", "completada", "cancelada"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluaciones obtenidas correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Evaluation")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay evaluaciones disponibles"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $filters = [
                'project' => $request->get('project'),
                'evaluator' => $request->get('evaluator'),
                'status' => $request->get('status')
            ];

            $evaluations = $this->evaluationService->getAllEvaluations($page, $perPage, $filters);

            // Siempre devolver 200 y la estructura de paginación, aunque esté vacío
            return response()->json([
                'status' => true,
                'message' => $evaluations->isEmpty() ? 'No hay evaluaciones disponibles' : 'Evaluaciones obtenidas correctamente',
                'data' => $evaluations->items(),
                'meta' => [
                    'current_page' => $evaluations->currentPage(),
                    'last_page' => $evaluations->lastPage(),
                    'per_page' => $evaluations->perPage(),
                    'total' => $evaluations->total()
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/evaluations",
     *     tags={"Evaluaciones"},
     *     summary="Crear una nueva evaluación",
     *     description="Crea una nueva evaluación y devuelve la evaluación creada",
     *     operationId="createEvaluation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="proyecto_id", type="integer"),
     *             @OA\Property(property="evaluador_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evaluación creada correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->createEvaluation($request->all());

            return $this->successResponse(
                $evaluation,
                'Evaluación creada correctamente',
                201
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/{id}",
     *     tags={"Evaluaciones"},
     *     summary="Obtener una evaluación por ID",
     *     description="Devuelve una evaluación específica por su ID",
     *     operationId="getEvaluationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluación obtenida correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evaluación no encontrada"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->findOrFail($id);
            return $this->successResponse(
                $evaluation,
                'Evaluación obtenida correctamente',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evaluación no encontrada',
                404
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/evaluations/{id}",
     *     tags={"Evaluaciones"},
     *     summary="Actualizar una evaluación",
     *     description="Actualiza una evaluación existente y devuelve la evaluación actualizada",
     *     operationId="updateEvaluation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="proyecto_id", type="integer"),
     *             @OA\Property(property="evaluador_id", type="integer"),
     *             @OA\Property(property="estado", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluación actualizada correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function update(UpdateEvaluationRequest $request, int $id): JsonResponse
    {
        try {
            $updatedEvaluation = $this->evaluationService->updateEvaluation($id, $request->all());

            return $this->successResponse(
                $updatedEvaluation,
                'Evaluación actualizada correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/evaluations/{id}",
     *     tags={"Evaluaciones"},
     *     summary="Eliminar una evaluación",
     *     description="Elimina una evaluación existente por su ID",
     *     operationId="deleteEvaluation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluación eliminada correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evaluación no encontrada"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->evaluationService->deleteEvaluation($id);

            return $this->successResponse(
                null,
                'Evaluación eliminada correctamente',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'Evaluación no encontrada',
                404
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/evaluations/{id}/cancel",
     *     tags={"Evaluaciones"},
     *     summary="Cancelar una evaluación",
     *     description="Marca una evaluación existente como cancelada por su ID",
     *     operationId="cancelEvaluation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluación cancelada correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $this->evaluationService->cancelEvaluation($id);

            return $this->successResponse(
                null,
                'Evaluación cancelada correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/evaluations/{id}/complete",
     *     tags={"Evaluaciones"},
     *     summary="Completar una evaluación",
     *     description="Marca una evaluación como completada y asigna puntajes",
     *     operationId="completeEvaluation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="dominio_tema", type="integer"),
     *             @OA\Property(property="manejo_auditorio", type="integer"),
     *             @OA\Property(property="comentarios", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluación completada correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function completeEvaluation(int $id, CompleteEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->markEvaluationAsCompleted($id, $request->all());

            return $this->successResponse(
                $evaluation,
                'Evaluación completada correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/evaluations/{id}/reassign",
     *     tags={"Evaluaciones"},
     *     summary="Reasignar evaluador a una evaluación",
     *     description="Reasigna un evaluador a una evaluación existente",
     *     operationId="reassignEvaluator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la evaluación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="evaluador_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluador reasignado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function reassign(ReassignEvaluatorRequest $request, int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->reassignEvaluator($id, $request['evaluador_id']);

            return $this->successResponse(
                $evaluation,
                'Evaluador reasignado correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/project/{projectId}",
     *     tags={"Evaluaciones"},
     *     summary="Obtener evaluaciones por ID de proyecto",
     *     description="Devuelve una lista de evaluaciones asociadas a un proyecto específico",
     *     operationId="getEvaluationsByProject",
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluaciones por proyecto obtenidas correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function byProject(int $projectId): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getEvaluationsByProject($projectId);

            return $this->successResponse(
                $evaluations,
                'Evaluaciones por proyecto obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/evaluator/{evaluatorId}",
     *     tags={"Evaluaciones"},
     *     summary="Obtener evaluaciones por ID de evaluador",
     *     description="Devuelve una lista de evaluaciones asociadas a un evaluador específico",
     *     operationId="getEvaluationsByEvaluator",
     *     @OA\Parameter(
     *         name="evaluatorId",
     *         in="path",
     *         required=true,
     *         description="ID del evaluador",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluaciones por evaluador obtenidas correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function byEvaluator(int $evaluatorId): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getEvaluationsByEvaluator($evaluatorId);

            return $this->successResponse(
                $evaluations,
                'Evaluaciones por evaluador obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/evaluator/{evaluatorId}/performance",
     *     tags={"Evaluaciones"},
     *     summary="Obtener rendimiento del evaluador",
     *     description="Devuelve el rendimiento de un evaluador específico",
     *     operationId="getEvaluatorPerformance",
     *     @OA\Parameter(
     *         name="evaluatorId",
     *         in="path",
     *         required=true,
     *         description="ID del evaluador",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rendimiento del evaluador obtenido correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function evaluatorPerformance(int $evaluatorId): JsonResponse
    {
        try {
            $performance = $this->evaluationService->getEvaluatorPerformance($evaluatorId);

            return $this->successResponse(
                $performance,
                'Rendimiento del evaluador obtenido correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/project/{projectId}/metrics",
     *     tags={"Evaluaciones"},
     *     summary="Obtener métricas por estado",
     *     description="Devuelve métricas de evaluación agrupadas por estado para un proyecto específico",
     *     operationId="getMetricsByStatus",
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Métricas por estado obtenidas correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function metricsByStatus(int $projectId): JsonResponse
    {
        try {
            $metrics = $this->evaluationService->getEvaluationMetricsByStatus($projectId);

            return $this->successResponse(
                $metrics,
                'Métricas por estado obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/status/{status}",
     *     tags={"Evaluaciones"},
     *     summary="Obtener evaluaciones por estado",
     *     description="Devuelve una lista de evaluaciones filtradas por estado",
     *     operationId="getEvaluationsByStatus",
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Estado de la evaluación (pendiente, en_proceso, completada, cancelada)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluaciones por estado obtenidas correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay evaluaciones disponibles para este estado"
     *     )
     * )
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getEvaluationsByStatus($status);

            if ($evaluations->isEmpty()) {
                return $this->errorResponse(
                    'No hay evaluaciones disponibles para este estado',
                    404
                );
            }

            return $this->successResponse(
                $evaluations,
                'Evaluaciones por estado obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/project/{projectId}/available-evaluators",
     *     tags={"Evaluaciones"},
     *     summary="Obtener evaluadores disponibles por ID de proyecto",
     *     description="Devuelve una lista de evaluadores disponibles para un proyecto específico",
     *     operationId="getAvailableEvaluators",
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID del proyecto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluadores disponibles obtenidos correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function availableEvaluators(int $projectId): JsonResponse
    {
        try {
            $evaluators = $this->evaluationService->getAvailableEvaluators($projectId);

            return $this->successResponse(
                $evaluators,
                'Evaluadores disponibles obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/evaluations/event/{eventId}/mass-assign",
     *     tags={"Evaluaciones"},
     *     summary="Asignar evaluadores a proyectos masivamente",
     *     description="Asigna evaluadores a múltiples proyectos de forma masiva",
     *     operationId="massAssignEvaluators",
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="assignments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="proyecto_id", type="integer"),
     *                     @OA\Property(property="evaluador_id", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluadores asignados correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function massAssign(MassAssignRequest $request, int $eventId): JsonResponse
    {
        try {
            $assignments = $this->evaluationService->assignEvaluatorsToProjects($eventId, $request['assignments']);

            return $this->successResponse(
                $assignments,
                'Evaluadores asignados correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/dashboard/stats",
     *     tags={"Evaluaciones"},
     *     summary="Obtener estadísticas del dashboard",
     *     description="Devuelve estadísticas del dashboard relacionadas con las evaluaciones",
     *     operationId="getDashboardStats",
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas del dashboard obtenidas correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            $stats = $this->evaluationService->getDashboardStats();

            return $this->successResponse(
                $stats,
                'Estadísticas del dashboard obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/event/{eventId}/report",
     *     tags={"Evaluaciones"},
     *     summary="Generar reporte de evaluación",
     *     description="Genera un reporte de evaluación para un evento específico",
     *     operationId="generateReport",
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reporte generado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function generateReport(int $eventId): JsonResponse
    {
        try {
            $report = $this->evaluationService->generateEventReport($eventId);

            return $this->successResponse(
                $report,
                'Reporte generado correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/event/{eventId}/unevaluated-projects",
     *     tags={"Evaluaciones"},
     *     summary="Obtener proyectos no evaluados por evento",
     *     description="Devuelve una lista de proyectos que necesitan evaluación para un evento específico",
     *     operationId="getUnevaluatedProjects",
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proyectos no evaluados obtenidos correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function unevaluatedProjects(int $eventId): JsonResponse
    {
        try {
            $projects = $this->evaluationService->getProjectsNeedingEvaluation($eventId);

            return $this->successResponse(
                $projects,
                'Proyectos no evaluados obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/evaluators",
     *     tags={"Evaluaciones"},
     *     summary="Obtener evaluadores por nombre",
     *     description="Devuelve una lista de evaluadores filtrados por nombre",
     *     operationId="getEvaluatorsByName",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="ID del evaluador",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evaluadores obtenidos correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function getEvaluatorsByName(Request $request): JsonResponse
    {
        try {
            $evaluators = $this->evaluationService->getEvaluatorsByName($request->get('id') ?? null);

            return $this->successResponse(
                $evaluators,
                'Evaluadores obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/evaluations/projects",
     *     tags={"Evaluaciones"},
     *     summary="Obtener todos los proyectos",
     *     description="Devuelve una lista de proyectos filtrados por título",
     *     operationId="getAllProjects",
     *     @OA\Parameter(
     *         name="titulo",
     *         in="query",
     *         description="Título del proyecto",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proyectos obtenidos correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud"
     *     )
     * )
     */
    public function getAllProjects(Request $request): JsonResponse
    {
        try {
            $projects = $this->evaluationService->getAllProjects($request->get('titulo') ?? null);

            return $this->successResponse(
                $projects,
                'Proyectos obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 400);
        }
    }

    protected function handleException(\Exception $e, int $statusCode = 500): JsonResponse
    {
        return $this->errorResponse(
            $e->getMessage(),
            $statusCode
        );
    }
}