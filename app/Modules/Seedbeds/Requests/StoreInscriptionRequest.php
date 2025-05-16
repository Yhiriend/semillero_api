<?php

namespace App\Modules\Seedbeds\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Se puede personalizar para roles, si es necesario
    }

    public function rules(): array
    {
        return [
            'semillero_id' => 'required|integer|exists:semillero,id',
            'usuario_id'   => 'required|integer|exists:usuario,id',
        ];
    }
}
