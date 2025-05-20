<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'contraseña' => [
                'required',
                'string',
                'min:6',
                function ($attribute, $value, $fail) {
                    if ($value !== $this->input('contraseña_confirmacion')) {
                        $fail('Las contraseñas no coinciden.');
                    }
                }
            ],
            'contraseña_confirmacion' => 'required|string|min:6',
            'tipo' => 'required|string|in:estudiante,profesor,administrador',
            'programa_id' => 'nullable|integer|exists:programa,id',
            'rol' => 'required|integer|exists:rol,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'email' => 'correo electrónico',
            'contraseña' => 'contraseña',
            'contraseña_confirmacion' => 'confirmación de contraseña',
            'tipo' => 'tipo de usuario',
            'programa_id' => 'programa',
            'rol' => 'rol',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'contraseña.required' => 'La contraseña es obligatoria.',
            'contraseña.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'contraseña_confirmacion.required' => 'La confirmación de la contraseña es obligatoria.',
            'contraseña_confirmacion.min' => 'La confirmación de la contraseña debe tener al menos 6 caracteres.',
            'tipo.required' => 'El tipo de usuario es obligatorio.',
            'tipo.in' => 'El tipo de usuario no es válido.',
            'programa_id.integer' => 'El programa debe ser un número entero.',
            'programa_id.exists' => 'El programa seleccionado no existe.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.integer' => 'El rol debe ser un número entero.',
            'rol.exists' => 'El rol seleccionado no existe.',
        ];
    }
} 