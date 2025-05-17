<?php

namespace App\Modules\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Modules\Reports\Models\Usuario;
use App\Modules\Reports\Models\Semillero;
use App\Modules\Reports\Models\SemilleroUsuario;
use App\Modules\Reports\Models\Proyecto;
use App\Modules\Reports\Models\Eventos;
use App\Modules\Reports\Models\Certificado;
use App\Modules\Reports\Models\Evaluaciones;

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
            // Validar existencia del usuario
            $usuario = Usuario::find($data['autor']);
            if (!$usuario) {
                throw new \Exception('Usuario no encontrado.');
            }
    
            // Validar que esté inscrito en el semillero
            $registro = SemilleroUsuario::where('usuario_id', $usuario->id)
                ->where('semillero_id', $data['semillero'])
                ->first();
    
            if (!$registro) {
                throw new \Exception('El usuario no está inscrito en el semillero.');
            }
    
            // Obtener nombre del semillero
            $semillero = Semillero::find($data['semillero']);
            if (!$semillero) {
                throw new \Exception('Semillero no encontrado.');
            }
    
            // Obtener proyecto relacionado al semillero
            $proyecto = Proyecto::where('semillero_id', $semillero->id)->first();
            if (!$proyecto) {
                throw new \Exception('No hay proyecto asociado al semillero.');
            }
    
            // Valores por defecto con datos reales
            $mergedData = array_merge([
                'autor'     => $usuario->nombre,
                'autor1'     => $usuario->nombre,
                'documento' => $data['documento'] ?? '00000000',
                'expedida'  => $data['expedida'] ?? 'Ciudad',
                'fecha'     => Carbon::now()->locale('es')->isoFormat('LL'),
                'codigo'    => strtoupper(substr(md5(uniqid(rand(), true)), 0, 10)),
                'semillero' => $semillero->nombre,
                'semillero1' => $semillero->nombre,
                'proyecto'  => $proyecto->titulo
            ], $data);
    
            // Generar el PDF
            $pdf = PDF::loadView('certificates.participation', $mergedData);
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
    
        } catch (\Exception $e) {
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
                a.estado AS actividad_estado,
                
                s.id AS semillero_id,
                s.nombre AS semillero_nombre,
                s.descripcion AS semillero_descripcion,
                
                p.id AS proyecto_id,
                p.titulo AS proyecto_titulo,
                
                ev.id AS evaluacion_id,
                ev.evaluador_id,
                ev.comentarios,
                ev.puntuacion,
                ev.puntaje_total,
                ev.estado AS evaluacion_estado,
                ev.fecha_creacion AS evaluacion_fecha_creacion,
                ev.fecha_actualizacion AS evaluacion_fecha_actualizacion,
                
                e.id AS evento_id,
                e.nombre AS evento_nombre,
                e.descripcion AS evento_descripcion,
                e.fecha_inicio AS evento_fecha_inicio,
                e.fecha_fin AS evento_fecha_fin,
                e.ubicacion
            FROM Actividad a
            LEFT JOIN Semillero s ON a.semillero_id = s.id
            LEFT JOIN Proyecto p ON a.proyecto_id = p.id
            LEFT JOIN Evaluacion ev ON ev.proyecto_id = p.id
            INNER JOIN Evento e ON a.evento_id = e.id
            WHERE e.id = ?
            ORDER BY a.fecha_inicio ASC, ev.id ASC
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

    public function generarCertificado(int $proyectoId, int $eventoId): ?Certificado
    {
        // Validamos que el proyecto esté inscrito en el evento
        $inscripcion = DB::table('proyecto_evento')
            ->where('proyecto_id', $proyectoId)
            ->where('evento_id', $eventoId)
            ->first();
    
        if (!$inscripcion) {
            return null;
        }
    
        $proyecto = Proyecto::findOrFail($proyectoId);
        $evento = Eventos::findOrFail($eventoId);
    
        // Obtenemos evaluaciones completadas del proyecto
        $evaluaciones = Evaluaciones::where('proyecto_id', $proyectoId)
            ->where('estado', 'completada')
            ->get()
            ->all();
    
        if (empty($evaluaciones)) {
            return null;
        }
    
        return new Certificado($proyecto, $evento, $evaluaciones);
    }
    
} 