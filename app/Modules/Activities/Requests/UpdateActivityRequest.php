<?php

namespace App\Modules\Activities\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        //return auth()->user()->hasAnyRole(['Administrador', 'Coordinador de Eventos']);
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => "sometimes|date",
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'estado' => 'sometimes|in:pendiente,en_progreso,completada,cancelada',
            'responsables' => 'nullable|array',
            'responsables.*' => 'exists:Usuario,id'
        ];
    }
}