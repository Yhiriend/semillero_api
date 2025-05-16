<?php

namespace App\Modules\Evaluations\Requests;

use App\Http\Requests\BaseRequest;

class UpdateEvaluationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'proyecto_id' => 'sometimes|integer|exists:Proyecto,id',
            'evaluador_id' => 'sometimes|integer|exists:Usuario,id',
            'comentarios' => 'nullable|string',
            'estado' => 'sometimes|string|in:pendiente,en_proceso,completada,cancelada',
            'fecha_asignacion' => 'sometimes|date',
            'fecha_completado' => 'sometimes|date'
        ];
    }
}