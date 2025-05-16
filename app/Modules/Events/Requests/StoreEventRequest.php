<?php

namespace App\Modules\Events\Requests;

use App\Http\Requests\BaseRequest;

class StoreEventRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'coordinador_id' => 'required|exists:Usuario,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'ubicacion' => 'nullable|string|max:200',
            'actividades' => 'nullable|array',
            'actividades.*.titulo' => 'required|string|max:200',
            'actividades.*.descripcion' => 'nullable|string',
            'actividades.*.fecha_inicio' => 'required|date|after_or_equal:fecha_inicio|before_or_equal:fecha_fin',
            'actividades.*.fecha_fin' => 'required|date|after:actividades.*.fecha_inicio|before_or_equal:fecha_fin',
            'actividades.*.responsables' => 'nullable|array',
            'actividades.*.responsables.*' => 'exists:Usuario,id',
        ];
    }

}