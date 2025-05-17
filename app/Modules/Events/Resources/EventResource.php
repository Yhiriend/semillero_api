<?php

namespace App\Modules\Events\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'coordinador' => $this->coordinador ? [
                'id' => $this->coordinador->id,
                'nombre' => $this->coordinador->nombre
            ] : null,
            'fecha_inicio' => $this->fecha_inicio->toDateTimeString(),
            'fecha_fin' => $this->fecha_fin->toDateTimeString(),
            'ubicacion' => $this->ubicacion,
            'actividades' => $this->whenLoaded('activities', function () {
                return $this->activities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'titulo' => $activity->titulo,
                        'descripcion' => $activity->descripcion,
                        'semillero_id' => $activity->semillero_id,
                        'proyecto_id' => $activity->proyecto_id,
                        'fecha_inicio' => $activity->fecha_inicio->toDateTimeString(),
                        'fecha_fin' => $activity->fecha_fin->toDateTimeString(),
                        'estado' => $activity->estado,
                        'responsables' => $activity->responsables->map(function ($responsable) {
                            return [
                                'id' => $responsable->id,
                                'nombre' => $responsable->nombre
                            ];
                        })->toArray()
                    ];
                });
            }),
        ];
    }
}