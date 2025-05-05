<?php

namespace App\Modules\Events\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        //return auth()->user()->hasAnyRole(['Administrador', 'Coordinador de Eventos']);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'string|max:200',
            'descripcion' => 'nullable|string',
            'coordinador_id' => 'exists:Usuario,id',
            'fecha_inicio' => 'date|after:now',
            'fecha_fin' => 'date|after:fecha_inicio',
            'ubicacion' => 'nullable|string|max:200',
        ];
    }
}