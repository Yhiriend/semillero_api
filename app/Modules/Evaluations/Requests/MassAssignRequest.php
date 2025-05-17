<?php

namespace App\Modules\Evaluations\Requests;

use App\Http\Requests\BaseRequest;

class MassAssignRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'assignments' => 'required|array',
            'assignments.*.proyecto_id' => 'required|integer|exists:Proyecto,id',
            'assignments.*.evaluador_id' => 'required|integer|exists:Usuario,id'
        ];
    }
}