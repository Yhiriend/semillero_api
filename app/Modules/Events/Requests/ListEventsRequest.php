<?php

namespace App\Modules\Events\Requests;

use App\Http\Requests\BaseRequest;


class ListEventsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'coordinador_nombre' => 'nullable|exists:Usuario,nombre',
        ];
    }
}