<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'estado' => $this->estado,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'semillero' => $this->whenLoaded('semillero', function() {
                return [
                    'id' => $this->semillero->id,
                    'titulo' => $this->semillero->titulo
                ];
            }),
            'lider' => $this->whenLoaded('lider', function() {
                return [
                    'id' => $this->lider->id,
                    'nombre' => $this->lider->nombre,
                    'email' => $this->lider->email
                ];
            }),
            'evaluaciones' => $this->when($request->incluir_evaluaciones, function() {
                return $this->evaluaciones->map(function($evaluacion) {
                    return [
                        'puntuacion' => $evaluacion->puntuacion,
                        'evaluador' => $evaluacion->evaluador->nombre,
                        'fecha' => $evaluacion->fecha_creacion
                    ];
                });
            }),
            'promedio_evaluaciones' => $this->when($request->incluir_evaluaciones, function() {
                return $this->evaluaciones->avg('puntuacion');
            })
        ];
    }
}