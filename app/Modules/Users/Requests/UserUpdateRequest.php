<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     @OA\Property(property="nombre", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="contraseña", type="string", format="password", example="password123"),
 *     @OA\Property(property="tipo", type="string", example="Estudiante"),
 *     @OA\Property(property="programa_id", type="integer", example=1)
 * )
 */
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