<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Models\Universidad;

class SemilleroService
{
    public function getSemillerosPorFiltros($universidadId, $facultadId = null, $programaId = null)
    {
        $query = Universidad::with([
            'facultades' => function ($q) use ($facultadId, $programaId) {
                if ($facultadId) {
                    $q->where('id', $facultadId);
                }

                $q->with(['programas' => function ($q2) use ($programaId) {
                    if ($programaId) {
                        $q2->where('id', $programaId);
                    }

                    $q2->with('semilleros');
                }]);
            }
        ])->where('id', $universidadId);

        return $query->get();
    }
}
