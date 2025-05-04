<?php

namespace App\Modules\Activities\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        //return auth()->user()->hasAnyRole(['Administrador', 'Coordinador de Eventos']);
    }

    public function rules(): array
    {
        $event = $this->route('event');
        return [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => "required|date|after_or_equal:{$event->fecha_inicio}|before_or_equal:{$event->fecha_fin}",
            'fecha_fin' => 'required|date|after:fecha_inicio|before_or_equal:'.$event->fecha_fin,
            'responsables' => 'nullable|array',
            'responsables.*' => 'exists:Usuario,id',
        ];
    }
}