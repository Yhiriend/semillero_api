<?php

namespace App\Modules\Projects\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'semillero_id' => $this->semillero_id,
            'lider_id' => $this->lider_id,
            'coordinador_id' => $this->coordinador_id,
            'estado' => $this->estado ?? 'activo',
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'semillero' => $this->whenLoaded('hotbet'),
        ];
    }
}