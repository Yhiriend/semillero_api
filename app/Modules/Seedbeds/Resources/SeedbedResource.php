<?php

namespace App\Modules\Seedbeds\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeedbedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->nombre,
            'description' => $this->descripcion,
            'status'      => $this->estado,
            'createdAt'   => $this->fecha_creacion,
            'updatedAt'   => $this->fecha_actualizacion,

            'coordinator' => [
                'id'   => $this->coordinador?->id,
                'name' => $this->coordinador?->name,
                'email'=> $this->coordinador?->email,
            ],

            'program' => [
                'id'   => $this->programa?->id,
                'name' => $this->programa?->nombre,
            ]
        ];
    }
}
