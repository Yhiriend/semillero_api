<?php

namespace App\Modules\Seedbeds\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'seedbedId'    => $this->semillero_id,
            'userId'       => $this->usuario_id,
            'registeredAt' => $this->fecha_inscripcion,
        ];
    }
}
