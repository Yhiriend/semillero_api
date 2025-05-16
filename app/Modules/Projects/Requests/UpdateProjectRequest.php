<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'semillero_id' => 'required|exists:semillero,id',
            'lider_id' => 'required|exists:usuario,id',
            'coordinador_id' => 'required|exists:usuario,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El titulo es requerido',
            'descripcion.required' => 'La descripcion es requerida',
            'semillero_id.required' => 'El semillero es requerido',
            'lider_id.required' => 'El lider es requerido',
            'coordinador_id.required' => 'El coordinador es requerido',
            'fecha_inicio.required' => 'La fecha de inicio es requerida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
        ];
    }
}