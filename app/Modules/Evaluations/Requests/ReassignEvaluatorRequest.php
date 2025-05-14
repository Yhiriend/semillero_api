<?php

namespace App\Modules\Evaluations\Requests;

use App\Http\Requests\BaseRequest;

class ReassignEvaluatorRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'evaluador_id' => 'required|integer|exists:Usuario,id'
        ];
    }

}