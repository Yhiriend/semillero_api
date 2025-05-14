<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Reports\Services\CertificateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @return JsonResponse
     */
    public function getProjectsWithAuthors(): JsonResponse
    {
        $projects = ProjectModel::with([
            'autores:id,nombre,email,tipo',
            'lider:id,nombre,email,tipo',
            'coordinador:id,nombre,email,tipo'
        ])->get();

        return $this->successResponse($projects, 'Lista de proyectos con sus autores obtenida exitosamente');
    }

    /**
     * Generate a participation certificate
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateCertificate(Request $request)
    {
        $data = $request->all();
        // Valores por defecto para evitar errores de variables indefinidas
        $defaults = [
            'autor'      => 'Nombre del estudiante',
            'documento'  => '00000000',
            'expedida'   => 'Ciudad',
            'semestre'   => 'I',
            'programa'   => 'Programa académico',
            'periodo'    => '2024-1',
            'porcentaje' => '100%',
            'fecha'      => now()->locale('es')->isoFormat('LL'),
            'codigo'     => strtoupper(substr(md5(uniqid(rand(), true)), 0, 10)),
        ];
        $data = array_merge($defaults, $data);

        $pdf = $this->certificateService->generateParticipationCertificate($data);
        return $pdf->download('certificado-participacion.pdf');
    }

    public function getEventReport(Request $request, int $eventoId): JsonResponse
{
    try {
        $report = $this->certificateService->getEventReport($eventoId);

        if (empty($report)) {
            return $this->errorResponse('No se encontraron actividades para este evento', 404);
        }

        return $this->successResponse($report, 'Reporte del evento generado exitosamente');
    } catch (\Exception $e) {
        return $this->errorResponse('Error al generar el reporte: ' . $e->getMessage(), 500);
    }
}

public function getProjectScores(Request $request): JsonResponse
{
    try {
        $scores = $this->certificateService->getProjectScores();

        if (empty($scores)) {
            return $this->errorResponse('No se encontraron evaluaciones completadas.', 404);
        }

        return $this->successResponse($scores, 'Calificaciones de proyectos obtenidas exitosamente');
    } catch (\Exception $e) {
        return $this->errorResponse('Error al obtener calificaciones: ' . $e->getMessage(), 500);
    }
}
} 