<?php

namespace App\Modules\Seedbeds\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'usuario_id' => $this->usuario_id,
            'semillero_id' => $this->semillero_id,
            'fecha_inscripcion' => $this->fecha_inscripcion,
            'student' => [
                'nombre' => $this->student->nombre,
                'email' => $this->student->email,
                'programa' => [
                    'nombre' => optional($this->student->programa)->nombre,
                ],
            ],
        ];
        
    }
}
