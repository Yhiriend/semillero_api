<?php

namespace App\Modules\Seedbeds\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeedbedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Se puede personalizar con lógica de autorización más adelante
    }

    public function rules(): array
    {
        return [
            'nombre'      => 'required|string',
            'descripcion' => 'required|string',
            'programa_id' => 'required|exists:programa,id',
            'profesor_id' => 'required|exists:usuario,id',
        ];
    }
}
