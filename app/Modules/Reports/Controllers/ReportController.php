<?php

namespace App\Modules\Reports\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Enums\ResponseCode;



use App\Http\Controllers\Controller;
use App\Modules\Reports\Models\ProjectReportModel;
use App\Modules\Reports\Services\CertificateService;
use App\Modules\Reports\Services\EventService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="API Endpoints for generating and managing reports"
 * )
 */
class ReportController extends Controller
{
    use ApiResponse;

    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Get all projects with their authors
     * 
     * @OA\Get(
     *     path="/api/reports/projects/with-authors",
     *     summary="Get all projects with their authors",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of projects with authors",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lista de proyectos con sus autores obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="titulo", type="string"),
     *                     @OA\Property(
     *                         property="autores",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="nombre", type="string"),
     *                             @OA\Property(property="email", type="string"),
     *                             @OA\Property(property="tipo", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getProjectsWithAuthors(Request $request): JsonResponse
    {
        try {
            // Obtener parámetros de paginación
            $perPage = $request->query('per_page', 10); // por defecto 10
            $page = $request->query('page', 1); // por defecto 1
    
            // Obtener proyectos con relaciones y paginar
            $projects = ProjectReportModel::with([
                'autores:id,nombre,email,tipo',
                'lider:id,nombre,email,tipo',
                'coordinador:id,nombre,email,tipo'
            ])->paginate($perPage, ['*'], 'page', $page);
    
            return $this->successResponse([
                'mensaje' => 'Lista de proyectos con sus autores obtenida exitosamente',
                'proyectos' => $projects
            ], ResponseCode::SUCCESS, 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function getProjectsBySemillero(Request $request): JsonResponse
    {
        try {
            // Obtener parámetros de filtro y paginación desde el request
            $semilleroId = $request->query('semillero_id'); // opcional
            $perPage = $request->query('per_page', 10); // valor por defecto: 10
            $page = $request->query('page', 1); // valor por defecto: 1

            // Construir la consulta base
            $query = ProjectReportModel::with([
                'autores:id,nombre,email,tipo',
                'lider:id,nombre,email,tipo',
                'coordinador:id,nombre,email,tipo'
            ]);

            // Si se envía un semillero_id, aplicar el filtro
            if ($semilleroId) {
                $query->where('semillero_id', $semilleroId);
            }

            // Obtener proyectos paginados
            $projects = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse([
                'mensaje' => 'Lista de proyectos filtrada y paginada',
                'proyectos' => $projects
            ], ResponseCode::SUCCESS, 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los proyectos: ' . $e->getMessage(), 500);
        }
    }
    

    /**
     * Generate a participation certificate
     * 
     * @OA\Post(
     *     path="/api/reports/certificates/generate",
     *     summary="Generate a participation certificate",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"autor", "documento", "expedida", "semillero"},
     *             @OA\Property(property="autor", type="integer", description="ID of the author"),
     *             @OA\Property(property="documento", type="string", description="Document number"),
     *             @OA\Property(property="expedida", type="string", description="Issue date"),
     *             @OA\Property(property="semillero", type="integer", description="ID of the seedbed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate generated successfully",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function generateCertificate(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'autor'     => 'required|integer|exists:Usuario,id',
                'documento' => 'required|string',
                'expedida'  => 'required|string',
                'semillero' => 'required|integer|exists:Semillero,id',
            ]);
    
            $pdf = $this->certificateService->generateParticipationCertificate($validated);
    
            return response()->download($pdf, 'certificado-participacion.pdf');
    
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
    
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, null, 404);
    
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_ERROR, $e->getMessage(), 500);
        }
    }
    

    /**
     * Get event report
     * 
     * @OA\Get(
     *     path="/api/reports/events/{eventId}/report",
     *     summary="Get detailed report for a specific event",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID of the event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No activities found for this event"
     *     )
     * )
     */
    public function getEventReport(Request $request, int $eventoId): JsonResponse
{
    try {
        $report = $this->certificateService->getEventReport($eventoId);

        if (empty($report)) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'No se encontraron actividades para este evento');
        }

        return $this->successResponse([
            'mensaje' => 'Reporte del evento generado exitosamente',
            'reporte' => $report
        ], ResponseCode::SUCCESS, 200);

    } catch (ModelNotFoundException $e) {
        return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'Evento no encontrado');
    } catch (\Exception $e) {
        return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al generar el reporte: ' . $e->getMessage());
    }
}
    /**
     * Get project scores
     * 
     * @OA\Get(
     *     path="/api/reports/projects/scores",
     *     summary="Get scores for all projects",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Project scores retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="project_id", type="integer"),
     *                     @OA\Property(property="project_title", type="string"),
     *                     @OA\Property(property="average_score", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No completed evaluations found"
     *     )
     * )
     */
    public function getProjectScores(Request $request): JsonResponse
    {
        try {
            $scores = $this->certificateService->getProjectScores();
    
            if (empty($scores)) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'No se encontraron evaluaciones completadas.');
            }
    
            return $this->successResponse([
                'mensaje' => 'Calificaciones de proyectos obtenidas exitosamente',
                'calificaciones' => $scores
            ], ResponseCode::SUCCESS, 200);
            
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al obtener calificaciones: ' . $e->getMessage());
        }
    }

    /**
     * Generate certificates for all participants in an event
     * 
     * @OA\Get(
     *     path="/api/reports/certificates/event/{eventId}/generate-all",
     *     summary="Generate certificates for all participants in an event",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID of the event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificates generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="evento", type="string"),
     *             @OA\Property(property="semillero", type="string"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(
     *                 property="certificados",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="nombre_participante", type="string"),
     *                     @OA\Property(property="correo", type="string"),
     *                     @OA\Property(property="nombre_evento", type="string"),
     *                     @OA\Property(property="fecha_inicio_evento", type="string", format="date-time"),
     *                     @OA\Property(property="fecha_fin_evento", type="string", format="date-time"),
     *                     @OA\Property(property="ubicacion_evento", type="string"),
     *                     @OA\Property(property="semillero", type="string"),
     *                     @OA\Property(property="fecha_inscripcion", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event or seedbed not found"
     *     )
     * )
     */

     public function generarCertificadosEvento($eventoId): JsonResponse
     {
         try {
             $evento = DB::table('Evento')->where('id', $eventoId)->first();
     
             if (!$evento) {
                 return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'Evento no encontrado');
             }
     
             $semillero = DB::table('Semillero')
                 ->where('coordinador_id', $evento->coordinador_id)
                 ->first();
     
             if (!$semillero) {
                 return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'No se encontró semillero asociado al coordinador del evento');
             }
     
             $participantes = DB::table('Semillero_usuario as su')
                 ->join('Usuario as u', 'su.usuario_id', '=', 'u.id')
                 ->select(
                     'u.id as usuario_id',
                     DB::raw("CONCAT(u.nombre) as nombre_participante"),
                     'u.email as correo',
                     'su.fecha_inscripcion'
                 )
                 ->where('su.semillero_id', $semillero->id)
                 ->get();
     
             $certificados = $participantes->map(function ($p) use ($evento, $semillero) {
                 return [
                     'nombre_participante' => $p->nombre_participante,
                     'correo'              => $p->correo,
                     'nombre_evento'       => $evento->nombre,
                     'fecha_inicio_evento' => $evento->fecha_inicio,
                     'fecha_fin_evento'    => $evento->fecha_fin,
                     'ubicacion_evento'    => $evento->ubicacion,
                     'semillero'           => $semillero->nombre,
                     'fecha_inscripcion'   => $p->fecha_inscripcion,
                 ];
             });
     
             return $this->successResponse([
                 'mensaje'      => 'Certificados generados exitosamente',
                 'evento'       => $evento->nombre,
                 'semillero'    => $semillero->nombre,
                 'total'        => $certificados->count(),
                 'certificados' => $certificados,
             ], ResponseCode::SUCCESS, 200);
     
         } catch (\Exception $e) {
             return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al generar los certificados: ' . $e->getMessage());
         }
     }
    
    /**
     * Get all activities
     * 
     * @OA\Get(
     *     path="/api/reports/events/{eventId}/activities",
     *     summary="Get all activities for a specific event",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID of the event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(
     *                 property="actividades",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="actividad_id", type="integer"),
     *                     @OA\Property(property="actividad_titulo", type="string"),
     *                     @OA\Property(property="descripcion", type="string"),
     *                     @OA\Property(property="fecha_inicio", type="string", format="date-time"),
     *                     @OA\Property(property="fecha_fin", type="string", format="date-time"),
     *                     @OA\Property(property="estado", type="string"),
     *                     @OA\Property(property="responsable_nombre", type="string"),
     *                     @OA\Property(property="responsable_correo", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */public function consultarActividades(): JsonResponse
{
    try {
        $actividades = DB::table('Actividad as a')
            ->leftJoin('Actividad_Responsable as ar', 'a.id', '=', 'ar.actividad_id')
            ->leftJoin('Usuario as u', 'ar.responsable_id', '=', 'u.id')
            ->select(
                'a.id as actividad_id',
                'a.titulo as actividad_titulo',
                'a.descripcion',
                'a.fecha_inicio',
                'a.fecha_fin',
                'a.estado',
                'a.fecha_creacion',
                'a.fecha_actualizacion',
                'a.semillero_id',
                'a.proyecto_id',
                'a.evento_id',
                'u.id as responsable_id',
                DB::raw("CONCAT(u.nombre) as responsable_nombre"),
                'u.email as responsable_correo'
            )
            ->orderBy('a.fecha_inicio', 'desc')
            ->get();

        return $this->successResponse([
            'mensaje'     => 'Actividades obtenidas exitosamente',
            'total'       => $actividades->count(),
            'actividades' => $actividades
        ], ResponseCode::SUCCESS, 200);

    } catch (\Exception $e) {
        return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al consultar las actividades: ' . $e->getMessage());
    }
}

    /**
     * Get certificate details for a specific project in an event
     * 
     * @OA\Get(
     *     path="/api/reports/certificates/project/{projectId}/event/{eventId}",
     *     summary="Get certificate details for a specific project in an event",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID of the project",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID of the event",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="proyecto", type="string"),
     *             @OA\Property(property="evento", type="string"),
     *             @OA\Property(property="promedio_puntuacion", type="number", format="float"),
     *             @OA\Property(
     *                 property="evaluaciones",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="evaluador_id", type="integer"),
     *                     @OA\Property(property="puntuacion", type="number", format="float"),
     *                     @OA\Property(property="comentarios", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No completed evaluations found for this project in the event"
     *     )
     * )
     */
    public function show($proyectoId, $eventoId): JsonResponse
    {
        try {
            $certificado = $this->certificateService->generarCertificado($proyectoId, $eventoId);
    
            if (!$certificado) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'No hay evaluaciones completadas para este proyecto en el evento');
            }
    
            $evaluaciones = collect($certificado->evaluations)->map(function ($eval) {
                return [
                    'evaluador_id' => $eval->evaluador_id,
                    'puntuacion'   => $eval->puntuacion,
                    'comentarios'  => $eval->comentarios,
                ];
            });
    
            return $this->successResponse([
                'mensaje'             => 'Certificado generado exitosamente',
                'evento'              => $certificado->event->nombre,
                'proyecto'            => $certificado->project->titulo,
                'promedio_puntuacion' => $certificado->averageScore,
                'total'               => $evaluaciones->count(),
                'evaluaciones'        => $evaluaciones,
            ], ResponseCode::SUCCESS, 200);
    
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 404, 'Proyecto o evento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::ERROR, 500, 'Error al generar el certificado: ' . $e->getMessage());
        }
    }
    
}