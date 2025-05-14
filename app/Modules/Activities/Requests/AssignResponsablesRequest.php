<?php

namespace App\Modules\Activities\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignResponsablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        //return auth()->user()->hasAnyRole(['Administrador', 'Coordinador de Eventos']);
    }

    public function rules(): array
    {
        return [
            'responsables' => 'required|array',
            'responsables.*' => 'exists:Usuario,id',
        ];
    }
}