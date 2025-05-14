<?php

namespace App\Modules\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;

class CertificateService
{
    /**
     * Generate a participation certificate
     *
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function generateParticipationCertificate(array $data)
    {
        try {
            // Valores por defecto
            $defaults = [
                'autor'      => 'Nombre del estudiante',
                'documento'  => '00000000',
                'expedida'   => 'Ciudad',
                'fecha'      => Carbon::now()->locale('es')->isoFormat('LL'),
                'codigo'     => strtoupper(substr(md5(uniqid(rand(), true)), 0, 10)),
                'semillero'  => 'Nombre del Semillero',
                'proyecto'   => 'Nombre del Proyecto'
            ];
            $data = array_merge($defaults, $data);

            // Generar el PDF
            $pdf = PDF::loadView('certificates.participation', $data);
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

            // Guardar el PDF
            $fileName = 'certificado_' . time() . '.pdf';
            Storage::disk('local')->put('certificates/' . $fileName, $pdf->output());

            return $pdf;
        } catch (Exception $e) {
            Log::error('Error generando certificado: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function getEventReport(int $eventoId): array
    {
        $sql = "
            SELECT 
                a.id AS actividad_id,
                a.titulo AS actividad_titulo,
                a.descripcion AS actividad_descripcion,
                a.fecha_inicio,
                a.fecha_fin,
                a.estado,
                s.id AS semillero_id,
                s.nombre AS semillero_nombre,
                s.descripcion AS semillero_descripcion,
                p.id AS proyecto_id,
                p.titulo AS proyecto_titulo,
                e.id AS evento_id,
                e.nombre AS evento_nombre,
                e.descripcion AS evento_descripcion,
                e.fecha_inicio AS evento_fecha_inicio,
                e.fecha_fin AS evento_fecha_fin,
                e.ubicacion
            FROM Actividad a
            LEFT JOIN Semillero s ON a.semillero_id = s.id
            LEFT JOIN Proyecto p ON a.proyecto_id = p.id
            INNER JOIN Evento e ON a.evento_id = e.id
            WHERE e.id = ?
            ORDER BY a.fecha_inicio ASC
        ";

        return DB::select($sql, [$eventoId]);
    }

    public function getProjectScores(): array
    {
        $sql = "
            SELECT 
                e.proyecto_id,
                p.titulo AS proyecto_titulo,
                COUNT(e.id) AS total_evaluaciones,
                ROUND(AVG(e.puntuacion), 1) AS promedio_puntuacion,
                SUM(e.puntaje_total) AS suma_puntaje_total
            FROM Evaluacion e
            INNER JOIN Proyecto p ON e.proyecto_id = p.id
            WHERE e.estado = 'completada'
            GROUP BY e.proyecto_id, p.titulo
            ORDER BY promedio_puntuacion DESC
        ";

        return DB::select($sql);
    }
} 