<?php

namespace App\Modules\Events\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'proyecto_id' => 'required|integer|min:1',
            'fecha_inscripcion' => 'required|date',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

}