<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UserStoreRequest",
 *     required={"nombre", "email", "contraseña", "tipo", "programa_id"},
 *     @OA\Property(property="nombre", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="contraseña", type="string", format="password", example="password123"),
 *     @OA\Property(property="tipo", type="string", example="Estudiante"),
 *     @OA\Property(property="programa_id", type="integer", example=1)
 * )
 */
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
            'email' => 'required|email|unique:Usuario,email',
            'contraseña' => 'required|string|min:6',
            'tipo' => 'required|string|in:Estudiante,Profesor,Administrador',
            'programa_id' => 'required|exists:Programa,id'
        ];
    }
} 