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
            'evento' => $this->whenLoaded('event'),
            'proyecto' => $this->whenLoaded('project'),
        ];
    }
}