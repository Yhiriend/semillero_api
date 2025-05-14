<?php

namespace App\Modules\Reports\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


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


    public function generarCertificadosEvento($eventoId)
    {

        $evento = DB::table('Evento')->where('id', $eventoId)->first();

        if (!$evento) {
            return response()->json(['error' => 'Evento no encontrado'], 404);
        }


        $semillero = DB::table('Semillero')
            ->where('coordinador_id', $evento->coordinador_id)
            ->first();

        if (!$semillero) {
            return response()->json(['error' => 'No se encontró semillero asociado al coordinador del evento'], 404);
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

        return response()->json([
            'evento'       => $evento->nombre,
            'semillero'    => $semillero->nombre,
            'total'        => $certificados->count(),
            'certificados' => $certificados,
        ]);
    }
    public function consultarActividades()
    {
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

        return response()->json([
            'total' => $actividades->count(),
            'actividades' => $actividades
        ]);
    }
}