<?php

namespace App\Modules\Evaluations\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'proyecto_id' => 'required|integer|exists:Proyecto,id',
            'evaluador_id' => 'required|integer|exists:Usuario,id',
            'comentarios' => 'nullable|string',
            'estado' => 'sometimes|string|in:pendiente,en_proceso,completada,cancelada',
            'fecha_asignacion' => 'sometimes|date'
        ];
    }
}