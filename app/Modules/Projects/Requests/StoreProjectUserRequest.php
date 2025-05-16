<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuario,id',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.required' => 'El estudiante es requerido',
        ];
    }
}
