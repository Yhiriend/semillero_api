<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'semillero_id' => 'required|exists:semillero,id',
            'lider_id' => 'required|exists:usuario,id',
            'coordinador_id' => 'required|exists:usuario,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El campo título es obligatorio.',
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'semillero_id.required' => 'El campo semillero es obligatorio.',
            'lider_id.required' => 'El campo líder es obligatorio.',
            'coordinador_id.required' => 'El campo coordinador es obligatorio.',
            'fecha_inicio.required' => 'El campo fecha de inicio es obligatorio.',
            'fecha_fin.required' => 'El campo fecha de fin es obligatorio.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}