<?php

namespace App\Modules\Evaluations\Requests;

use App\Http\Requests\BaseRequest;

class CompleteEvaluationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'dominio_tema' => 'required|numeric|min:0|max:5',
            'manejo_auditorio' => 'required|numeric|min:0|max:5',
            'planteamiento_problema' => 'required|numeric|min:0|max:5',
            'justificacion' => 'required|numeric|min:0|max:5',
            'objetivo_general' => 'required|numeric|min:0|max:5',
            'objetivo_especifico' => 'required|numeric|min:0|max:5',
            'marco_teorico' => 'required|numeric|min:0|max:5',
            'metodologia' => 'required|numeric|min:0|max:5',
            'resultado_esperado' => 'required|numeric|min:0|max:5',
            'referencia_bibliografica' => 'required|numeric|min:0|max:5',
            'comentarios' => 'nullable|string',
            'puntaje_total' => 'sometimes|numeric|min:0'
        ];
    }
}