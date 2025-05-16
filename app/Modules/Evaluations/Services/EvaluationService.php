<?php

namespace App\Modules\Evaluations\Services;

use App\Modules\Evaluations\Repositories\EvaluationRepository;
use Exception;

class EvaluationService
{
    public function __construct(protected EvaluationRepository $evaluationRepository)
    {
    }

    public function findOrFail(int $id)
    {
        $evaluation = $this->evaluationRepository->findOrFail($id);
        if (!$evaluation) {
            throw new Exception('Evaluación no encontrada');
        }
        return $evaluation;
    }

    protected function validateEvaluationData(array $data, ?int $evaluationId = null): void
    {
        if (!$this->evaluationRepository->hasConflictProject($data['proyecto_id'])) {
            throw new Exception('El proyecto no pertenece a un evento válido');
        }

        if (!$this->evaluationRepository->hasConflictEvaluador($data['evaluador_id'])) {
            throw new Exception('El usuario asignado no es un evaluador válido o tiene un conflicto');
        }
    }

    public function getAvailableEvaluators(int $projectId)
    {
        $currentEvaluators = $this->evaluationRepository
            ->getEvaluatorsByProjectId($projectId)
            ->pluck('id');

        return $this->evaluationRepository->getEvaluators()
            ->whereNotIn('id', $currentEvaluators);
    }

    public function getAllEvaluations()
    {
        return $this->evaluationRepository->getAllEvaluations();
    }

    public function createEvaluation(array $data)
    {
        $this->validateEvaluationData($data);

        if (!$this->evaluationRepository->isEventActiveForProject($data['proyecto_id'])) {
            throw new Exception('No se puede crear una evaluación para un proyecto con evento inactivo');
        }

        if (!isset($data['estado'])) {
            $data['estado'] = 'pendiente';
        }

        if (!isset($data['fecha_asignacion'])) {
            $data['fecha_asignacion'] = now();
        }

        return $this->evaluationRepository->createEvaluation($data);
    }

    public function updateEvaluation(int $id, array $data)
    {
        if (isset($data['proyecto_id']) || isset($data['evaluador_id'])) {
            $currentEvaluation = $this->evaluationRepository->findOrFail($id);
            $validationData = [
                'proyecto_id' => $data['proyecto_id'] ?? $currentEvaluation->proyecto_id,
                'evaluador_id' => $data['evaluador_id'] ?? $currentEvaluation->evaluador_id,
            ];

            $this->validateEvaluationData($validationData, $id);
        }

        return $this->evaluationRepository->updateEvaluation($id, $data);
    }

    public function deleteEvaluation(int $id)
    {
        $evaluation = $this->evaluationRepository->findOrFail($id);

        if (
            $evaluation->estado === 'completada' &&
            $this->evaluationRepository->projectHasCompletedEvaluations($evaluation->proyecto_id)
        ) {
            throw new Exception('No se puede eliminar una evaluación completada');
        }

        return $this->evaluationRepository->deleteEvaluation($id);
    }

    public function cancelEvaluation(int $id)
    {
        $evaluation = $this->evaluationRepository->findOrFail($id);

        if ($evaluation->estado === 'completada') {
            throw new Exception('No se puede cancelar una evaluación ya completada');
        }

        return $this->evaluationRepository->cancelEvaluation($id);
    }

    public function getEvaluationsByProject(int $projectId)
    {
        return $this->evaluationRepository->getEvaluationsByProjectId($projectId);
    }

    public function getEvaluationsByEvaluator(int $evaluatorId)
    {
        return $this->evaluationRepository->getEvaluationsByEvaluatorId($evaluatorId);
    }

    public function getEvaluationsByEvent(int $eventId, ?int $perPage = null)
    {
        if (!$this->evaluationRepository->eventExists($eventId)) {
            throw new Exception('El evento especificado no existe');
        }

        return $this->evaluationRepository->getEvaluationsByEvent($eventId, $perPage);
    }

    public function generateEventReport(int $eventId)
    {
        if (!$this->evaluationRepository->eventExists($eventId)) {
            throw new Exception('El evento especificado no existe');
        }

        return $this->evaluationRepository->generateReport($eventId);
    }

    public function getEvaluationMetricsByStatus(int $projectId)
    {
        return $this->evaluationRepository->getEvaluationStatusCount($projectId);
    }

    public function getEvaluatorPerformance(int $evaluatorId)
    {
        if (!$this->evaluationRepository->hasConflictEvaluador($evaluatorId)) {
            throw new Exception('El evaluador especificado no existe o no tiene el rol adecuado');
        }

        $evaluations = $this->evaluationRepository->getEvaluationsByEvaluatorId($evaluatorId);
        $averageScore = $this->evaluationRepository->getAverageScoreByEvaluatorId($evaluatorId);

        return [
            'total_evaluaciones' => $evaluations->count(),
            'evaluaciones_completadas' => $evaluations->where('estado', 'completada')->count(),
            'evaluaciones_pendientes' => $evaluations->where('estado', 'pendiente')->count(),
            'puntaje_promedio_otorgado' => $averageScore,
        ];
    }

    public function getProjectsNeedingEvaluation(int $eventId)
    {
        if (!$this->evaluationRepository->eventExists($eventId)) {
            throw new Exception('El evento especificado no existe');
        }

        if (!$this->evaluationRepository->isEventActive($eventId)) {
            throw new Exception('El evento especificado no está activo');
        }

        return $this->evaluationRepository->getUnevaluatedProjects($eventId);
    }

    public function getEvaluationsByStatus(string $status)
    {
        $validStatuses = ['pendiente', 'en_proceso', 'completada', 'cancelada'];

        if (!in_array($status, $validStatuses)) {
            throw new Exception('Estado de evaluación no válido');
        }

        return $this->evaluationRepository->getEvaluationsByStatus($status);
    }

    public function markEvaluationAsCompleted(int $id, array $scores)
    {
        $evaluation = $this->evaluationRepository->findOrFail($id);

        if (!in_array($evaluation->estado, ['pendiente', 'en_proceso'])) {
            throw new Exception('Solo se pueden completar evaluaciones pendientes o en proceso');
        }

        if (!$this->evaluationRepository->isEventActiveForProject($evaluation->proyecto_id)) {
            throw new Exception('No se puede completar una evaluación para un proyecto con evento inactivo');
        }

        $criteria = [
            'dominio_tema',
            'manejo_auditorio',
            'planteamiento_problema',
            'justificacion',
            'objetivo_general',
            'objetivo_especifico',
            'marco_teorico',
            'metodologia',
            'resultado_esperado',
            'referencia_bibliografica'
        ];

        foreach ($criteria as $criterion) {
            if (isset($scores[$criterion]) && ($scores[$criterion] < 0 || $scores[$criterion] > 5)) {
                throw new Exception("El puntaje para $criterion debe estar entre 0 y 5");
            }
        }

        $updateData = array_merge($scores, [
            'estado' => 'completada',
            'fecha_completado' => now(),
        ]);

        $evaluation = $this->evaluationRepository->updateEvaluation($id, $updateData);
        $evaluationId = $evaluation->id;
        return $this->evaluationRepository->findOrFail($evaluationId);
    }

    public function assignEvaluatorsToProjects(int $eventId, array $assignments)
    {
        if (!$this->evaluationRepository->eventExists($eventId)) {
            throw new Exception('El evento especificado no existe');
        }

        if (!$this->evaluationRepository->isEventActive($eventId)) {
            throw new Exception('El evento especificado no está activo');
        }

        $createdEvaluations = [];

        foreach ($assignments as $assignment) {
            if (!isset($assignment['proyecto_id']) || !isset($assignment['evaluador_id'])) {
                throw new Exception('Datos de asignación incompletos');
            }

            $projectBelongsToEvent = $this->evaluationRepository->hasConflictProject($assignment['proyecto_id']);
            if (!$projectBelongsToEvent) {
                throw new Exception('El proyecto con id: ' . $assignment['proyecto_id'] . ' no pertenece a un evento válido ');
            }

            if (!$this->evaluationRepository->hasConflictEvaluador($assignment['evaluador_id'])) {
                throw new Exception('El evaluador asignado no es válido o tiene un conflicto');
            }

            $evaluationData = [
                'proyecto_id' => $assignment['proyecto_id'],
                'evaluador_id' => $assignment['evaluador_id'],
                'estado' => 'pendiente',
                'fecha_asignacion' => now(),
            ];

            $createdEvaluations[] = $this->createEvaluation($evaluationData);
        }

        return $createdEvaluations;
    }

    public function reassignEvaluator(int $id, int $newEvaluatorId)
    {
        $evaluation = $this->evaluationRepository->findOrFail($id);

        if ($evaluation->estado !== 'pendiente') {
            throw new Exception('Solo se pueden reasignar evaluaciones pendientes');
        }

        $data = [
            'proyecto_id' => $evaluation->proyecto_id,
            'evaluador_id' => $newEvaluatorId,
        ];

        $this->validateEvaluationData($data, $id);

        return $this->evaluationRepository->updateEvaluation($id, ['evaluador_id' => $newEvaluatorId]);
    }

    public function getDashboardStats()
    {
        $pending = $this->evaluationRepository->getEvaluationsByStatus('pendiente')->count();
        $completed = $this->evaluationRepository->getEvaluationsByStatus('completada')->count();
        $cancelled = $this->evaluationRepository->getEvaluationsByStatus('cancelada')->count();
        $inProcess = $this->evaluationRepository->getEvaluationsByStatus('en_proceso')->count();

        return [
            'Pendientes' => $pending,
            'Completadas' => $completed,
            'Canceladas' => $cancelled,
            'En Proceso' => $inProcess,
            'Total' => $pending + $completed + $cancelled + $inProcess,
        ];
    }
}