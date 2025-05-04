<?php

namespace App\Modules\Events\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'proyecto_id' => 'required|integer|min:1',
            'fecha_inscripcion' => 'required|date',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'proyecto_id.required' => 'El ID del proyecto es requerido',
            'proyecto_id.integer' => 'El ID del proyecto debe ser un número entero',
            'proyecto_id.min' => 'El ID del proyecto debe ser positivo',
            'fecha_inscripcion.required' => 'La fecha de inscripción es requerida',
            'fecha_inscripcion.date' => 'La fecha de inscripción debe ser válida'
        ];
    }
}