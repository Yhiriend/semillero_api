<?php

namespace App\Modules\Reports\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;

class EventInscriptionService
{
    /**
     * Get all students enrolled in events with their event details
     *
     * @return Collection
     * @throws QueryException
     */
    public function getEnrolledStudents(): Collection
    {
        $query = DB::table('Usuario as u')
            ->join('Usuario_Rol as ur', 'u.id', '=', 'ur.usuario_id')
            ->join('Proyecto_Usuario as pu', 'u.id', '=', 'pu.usuario_id')
            ->join('Proyecto as p', 'pu.proyecto_id', '=', 'p.id')
            ->join('Proyecto_Evento as pe', 'p.id', '=', 'pe.proyecto_id')
            ->join('Evento as e', 'pe.evento_id', '=', 'e.id')
            ->where('u.tipo', 'estudiante')
            ->whereIn('ur.rol_id', [1, 2])
            ->select([
                'u.id as estudiante_id',
                'u.nombre as estudiante_nombre',
                'u.email as estudiante_email',
                'e.id as evento_id',
                'e.nombre as evento_nombre',
                'e.fecha_inicio as evento_fecha_inicio',
                'e.fecha_fin as evento_fecha_fin',
                'p.id as proyecto_id',
                'p.titulo as proyecto_titulo',
                'pe.fecha_inscripcion'
            ]);

        $results = $query->get();

        if ($results->isEmpty()) {
            return collect([]);
        }

        return $results->groupBy('estudiante_id')
            ->map(function ($inscriptions) {
                $firstInscription = $inscriptions->first();
                return [
                    'estudiante' => [
                        'id' => $firstInscription->estudiante_id,
                        'nombre' => $firstInscription->estudiante_nombre,
                        'email' => $firstInscription->estudiante_email
                    ],
                    'eventos' => $inscriptions->map(function ($inscription) {
                        return [
                            'id' => $inscription->evento_id,
                            'nombre' => $inscription->evento_nombre,
                            'fecha_inicio' => $inscription->evento_fecha_inicio,
                            'fecha_fin' => $inscription->evento_fecha_fin,
                            'proyecto' => [
                                'id' => $inscription->proyecto_id,
                                'titulo' => $inscription->proyecto_titulo
                            ],
                            'fecha_inscripcion' => $inscription->fecha_inscripcion
                        ];
                    })->values()
                ];
            })->values();
    }
} 