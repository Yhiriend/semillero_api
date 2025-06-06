<?php

namespace App\Modules\Events\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectEventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'evento_id' => $this->evento_id,
            'proyecto_id' => $this->proyecto_id,
            'fecha_inscripcion' => $this->fecha_inscripcion->toDateTimeString(),
            'observaciones' => $this->observaciones,
            'nombre_evento' => $this->event->nombre ?? null,
            'nombre_proyecto' => $this->project->titulo ?? null,
            
            'evento' => [
                'nombre' => $this->event->nombre ?? null,
                'fecha_inicio' => $this->event->fecha_inicio ?? null,
            ],
            'proyecto' => [
                'nombre' => $this->project->titulo ?? null,
            ]
        ];
    }
}