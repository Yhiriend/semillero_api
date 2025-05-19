<?php

namespace App\Modules\Reports\Models;

use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Events\Models\EventModel;
use App\Modules\Evaluations\Models\EvaluationModel;

class CertificateModel
{
    public ProjectModel $project;
    public EventModel $event;
    public array $evaluations;
    public float $averageScore;

    public function __construct(ProjectModel $project, EventModel $event, array $evaluations)
    {
        $this->project = $project;
        $this->event = $event;
        $this->evaluations = $evaluations;
        $this->averageScore = $this->calculateAverage();
    }

    protected function calculateAverage(): float
    {
        $sum = 0;
        $total = count($this->evaluations);

        foreach ($this->evaluations as $eval) {
            $sum += $eval->puntuacion;
        }

        return $total > 0 ? round($sum / $total, 1) : 0;
    }
} 