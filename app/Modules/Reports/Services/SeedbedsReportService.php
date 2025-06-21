<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Models\Universidad;
use App\Modules\Reports\Models\UniversityReportModel;
use App\Modules\Reports\Models\SeedbedModel;

class SeedbedsReportService
{
    public function getSemillerosPorFiltros($universidadId, $facultadId = null, $programaId = null)
    {
        $query = UniversityReportModel::with([
            'faculties' => function ($q) use ($facultadId, $programaId) {
                if ($facultadId) {
                    $q->where('id', $facultadId);
                }

                $q->with(['programas' => function ($q2) use ($programaId) {
                    if ($programaId) {
                        $q2->where('id', $programaId);
                    }

                    $q2->with('seedbeds');
                }]);
            }
        ])->where('id', $universidadId);

        return $query->get();
    }

    public function getUsersBySeedbed(int $semilleroId): array
    {
        $semillero = SeedbedModel::with('users')->find($semilleroId);

        if (!$semillero) {
            return ['error' => 'Semillero no encontrado'];
        }

        $usuarios = $semillero->users->map(function ($user) {
            return [
                'id'                => $user->id,
                'nombre'           => $user->nombre,
                'email'            => $user->email,
                'tipo'             => $user->tipo,
                'programa_id'      => $user->programa_id,
                'fecha_inscripcion'=> $user->pivot->fecha_inscripcion ?? null,
            ];
        });

        return [
            'semillero' => [
                'id'     => $semillero->id,
                'nombre' => $semillero->nombre,
            ],
            'usuarios' => $usuarios,
        ];
    }

    public function getAllSemilleros()
{
    return SeedbedModel::with(['users'])->get();
}

}
