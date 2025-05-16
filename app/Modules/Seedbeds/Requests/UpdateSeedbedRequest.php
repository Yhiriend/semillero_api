<?php

namespace App\Modules\Seedbeds\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeedbedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
