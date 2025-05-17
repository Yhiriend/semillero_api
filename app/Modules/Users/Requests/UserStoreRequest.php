<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario,email',
            'contraseña' => 'required|string|min:6',
            'tipo' => 'required|string|in:estudiante,profesor,administrador',
            'programa_id' => 'nullable|integer|exists:programa,id',
        ];
    }
} 