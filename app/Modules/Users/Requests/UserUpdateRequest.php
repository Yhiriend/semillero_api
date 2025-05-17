<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'nombre' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:usuario,email,' . $id,
            'contraseña' => 'sometimes|required|string|min:6',
            'tipo' => 'sometimes|required|string|in:estudiante,profesor,administrador',
            'programa_id' => 'nullable|integer|exists:programa,id',
        ];
    }
} 