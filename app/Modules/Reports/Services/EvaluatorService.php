<?php

namespace App\Modules\Reports\Services;

use App\Modules\Evaluations\Models\EvaluationModel;
use Illuminate\Support\Collection;

class EvaluatorService
{
    /**
     * Get all evaluators with their assigned projects
     *
     * @return Collection
     */
    public function getEvaluatorsWithProjects(): Collection
    {
        return EvaluationModel::with(['evaluador:id,nombre,email', 'proyecto:id,titulo'])
            ->select('evaluador_id')
            ->distinct()
            ->get()
            ->map(function ($evaluation) {
                return [
                    'evaluador' => $evaluation->evaluador,
                    'proyectos' => $evaluation->evaluador->evaluaciones()
                        ->with('proyecto:id,titulo')
                        ->get()
                        ->pluck('proyecto')
                ];
            });
    }
} 