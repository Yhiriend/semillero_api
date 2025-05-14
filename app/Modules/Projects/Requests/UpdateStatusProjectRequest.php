<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:activo,inactivo,completado,cancelado',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'El estado es requerido',
            'status.in' => 'El estado debe ser uno de los siguientes: activo, inactivo, completado, cancelado',
        ];
    }
}