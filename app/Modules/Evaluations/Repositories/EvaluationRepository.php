<?php

namespace App\Modules\Evaluations\Repositories;

use App\Modules\Evaluations\Models\EvaluationModel;
use Illuminate\Support\Facades\DB;

class EvaluationRepository
{
    public function getAllEvaluations()
    {
        return EvaluationModel::with('project', 'evaluator')->get();
    }

    public function createEvaluation(array $data)
    {
        return EvaluationModel::create($data);
    }

    public function findOrFail(int $id)
    {
        return EvaluationModel::findOrFail($id);
    }

    public function updateEvaluation(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $evaluation = $this->findOrFail($id);
            $evaluation->update($data);
            return $evaluation;
        });
    }
    public function deleteEvaluation(int $id)
    {
        return DB::transaction(function () use ($id) {
            $evaluation = $this->findOrFail($id);
            return $evaluation->delete();
        });
    }

    public function cancelEvaluation(int $evaluationId)
    {
        return DB::transaction(function () use ($evaluationId) {
            return DB::table('Evaluacion')
                ->where('id', $evaluationId)
                ->update(['estado' => 'cancelada']);
        });
    }

    public function getEvaluators()
    {
        return DB::table('Usuario')
            ->join('Usuario_Rol', 'Usuario.id', '=', 'Usuario_Rol.usuario_id')
            ->join('Programa', 'Usuario.programa_id', '=', 'Programa.id')
            ->join('Facultad', 'Programa.facultad_id', '=', 'Facultad.id')
            ->join('Universidad', 'Facultad.universidad_id', '=', 'Universidad.id')
            ->where('Usuario.tipo', 'profesor')
            ->where('Usuario_Rol.rol_id', 7) // EVALUADOR
            ->select(
                'Usuario.id',
                'Usuario.nombre',
                'Usuario.email',
                'Usuario.tipo',
                'Programa.nombre as programa',
                'Facultad.nombre as facultad',
                'Universidad.nombre as universidad'
            )
            ->get();
    }

    public function getEvaluatorsByProjectId(int $projectId)
    {
        return DB::table('Usuario')
            ->join('Usuario_Rol', 'Usuario.id', '=', 'Usuario_Rol.usuario_id')
            ->join('Evaluacion', 'Usuario.id', '=', 'Evaluacion.evaluador_id')
            ->join('Programa', 'Usuario.programa_id', '=', 'Programa.id')
            ->join('Facultad', 'Programa.facultad_id', '=', 'Facultad.id')
            ->join('Universidad', 'Facultad.universidad_id', '=', 'Universidad.id')
            ->where('Usuario.tipo', 'profesor')
            ->where('Usuario_Rol.Rol_id', 7)
            ->where('Evaluacion.proyecto_id', $projectId)
            ->select(
                'Usuario.id',
                'Usuario.nombre',
                'Usuario.email',
                'Usuario.tipo',
                'Programa.nombre as programa',
                'Facultad.nombre as facultad',
                'Universidad.nombre as universidad'
            )
            ->get();
    }


    public function getEvaluationsByProjectId(int $projectId)
    {
        return EvaluationModel::where('proyecto_id', $projectId)
            ->with('project', 'evaluator')
            ->get();
    }

    public function getEvaluationsByEvaluatorId(int $evaluatorId)
    {
        return EvaluationModel::where('evaluador_id', $evaluatorId)
            ->with('project', 'evaluator')
            ->get();
    }

    public function getEvaluationsByEvent(int $eventId, ?int $perPage = null)
    {
        $query = DB::table('Evaluacion')
            ->join('Proyecto_Evento', 'Evaluacion.proyecto_id', '=', 'Proyecto_Evento.proyecto_id')
            ->where('Proyecto_Evento.evento_id', $eventId)
            ->select('Evaluacion.*');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function isEventActive(int $eventId)
    {
        return DB::table('Evento')
            ->where('id', $eventId)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->exists();
    }

    public function isEventActiveForProject(int $projectId)
    {
        return DB::table('Proyecto_Evento')
            ->join('Evento', 'Proyecto_Evento.evento_id', '=', 'Evento.id')
            ->join('Proyecto', 'Proyecto_Evento.proyecto_id', '=', 'Proyecto.id')
            ->where('Proyecto_Evento.proyecto_id', $projectId)
            ->where('Proyecto.estado', '!=', 'cancelado')
            ->exists();
    }

    public function eventExists(int $eventId)
    {
        return DB::table('Evento')->where('id', $eventId)->exists();
    }

    public function generateReport(int $eventId)
    {
        return DB::table('Evaluacion')
            ->join('Proyecto_Evento', 'Evaluacion.proyecto_id', '=', 'Proyecto_Evento.proyecto_id')
            ->where('Proyecto_Evento.evento_id', $eventId)
            ->groupBy('Evaluacion.proyecto_id')
            ->select(
                'Evaluacion.proyecto_id',
                DB::raw('AVG(Evaluacion.puntaje_total) as averageScore'),
                DB::raw('MAX(Evaluacion.puntaje_total) as maxScore'),
                DB::raw('MIN(Evaluacion.puntaje_total) as minScore')
            )
            ->get();
    }

    public function getEvaluationsByStatus(string $status)
    {
        return EvaluationModel::where('estado', $status)->get();
    }


    public function hasConflictProject(int $projectId)
    {
        return DB::table('Proyecto_Evento')
            ->where('proyecto_id', $projectId)
            ->exists();
    }

    public function hasConflictEvaluador(int $evaluadorId)
    {
        return DB::table('Usuario')
            ->join('Usuario_Rol', 'Usuario.id', '=', 'Usuario_Rol.usuario_id')
            ->where('Usuario.id', $evaluadorId)
            ->where('Usuario.tipo', 'profesor')
            ->where('Usuario_Rol.rol_id', 7)  // Se restringe a los evaluadores
            ->exists(); // Devuelve true si existe el evaluador o false si no lo es.
    }

    public function getAverageScoreByEvaluatorId(int $evaluatorId)
    {
        return DB::table('Evaluacion')
            ->where('evaluador_id', $evaluatorId)
            ->avg('puntaje_total');
    }

    public function getEvaluationStatusCount(int $projectId)
    {
        return EvaluationModel::where('proyecto_id', $projectId)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
    }

    public function projectHasCompletedEvaluations(int $projectId)
    {
        return EvaluationModel::where('proyecto_id', $projectId)
            ->where('estado', 'completada')
            ->exists();
    }

    public function getUnevaluatedProjects(int $eventId)
    {
        return DB::table('Proyecto_Evento as pe')
            ->leftJoin('Evaluacion as e', function ($join) {
                $join->on('pe.proyecto_id', '=', 'e.proyecto_id');
            })
            ->where('pe.evento_id', $eventId)
            ->whereNull('e.id')
            ->pluck('pe.proyecto_id');
    }
}