<?php

namespace App\Modules\Reports\Models;

use App\Modules\Reports\Models\Evaluacion;
use App\Modules\Reports\Models\Proyecto;
use App\Modules\Reports\Models\Eventos;

class Certificado
{
    public Proyecto $proyecto;
    public Eventos $evento;
    public array $evaluaciones;
    public float $promedioPuntaje;

    public function __construct(Proyecto $proyecto, Eventos $evento, array $evaluaciones)
    {
        $this->proyecto = $proyecto;
        $this->evento = $evento;
        $this->evaluaciones = $evaluaciones;
        $this->promedioPuntaje = $this->calcularPromedio();
    }

    protected function calcularPromedio(): float
    {
        $suma = 0;
        $total = count($this->evaluaciones);

        foreach ($this->evaluaciones as $eval) {
            $suma += $eval->puntuacion;
        }

        return $total > 0 ? round($suma / $total, 1) : 0;
    }
}
