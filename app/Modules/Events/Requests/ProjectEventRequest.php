<?php

namespace App\Modules\Events\Requests;

use App\Http\Requests\BaseRequest;

class ProjectEventRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'proyecto_id' => 'required|integer|min:1',
            'fecha_inscripcion' => 'required|date',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

}