<?php

namespace App\Modules\Events\Resources;

use App\Modules\Activities\Resources\ActivityResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'coordinador' => $this->coordinador ? $this->coordinador->nombre : null,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'ubicacion' => $this->ubicacion,
            'actividades' => ActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}