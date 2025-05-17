<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'contraseña' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'contraseña' => 'contraseña',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'contraseña.required' => 'La contraseña es obligatoria.',
        ];
    }
} 