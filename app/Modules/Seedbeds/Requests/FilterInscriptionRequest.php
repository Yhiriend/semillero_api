<?php

namespace App\Modules\Seedbeds\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semillero_id' => 'nullable|integer|exists:semillero,id',
            'usuario_id'   => 'nullable|integer|exists:usuario,id',
        ];
    }
}
