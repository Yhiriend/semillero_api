<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints para la autenticación de usuarios"
 * )
 */
class AuthController
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Autenticación"},
     *     summary="Registrar un nuevo usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","tipo"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@ejemplo.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="123456"),
     *             @OA\Property(property="tipo", type="string", enum={"estudiante","profesor","administrador"}, example="estudiante"),
     *             @OA\Property(property="programa_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario registrado correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario,email',
            'password' => 'required|string|min:6|confirmed',
            'tipo' => 'required|string|in:estudiante,profesor,administrador',
            'programa_id' => 'nullable|integer|exists:programa,id',
        ]);

        $result = $this->authService->register($request->all());

        return $this->successResponse($result, 'Usuario registrado correctamente', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Autenticación"},
     *     summary="Iniciar sesión",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@ejemplo.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login exitoso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $result = $this->authService->login($request->email, $request->password);
            return $this->successResponse($result, 'Login exitoso');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error al iniciar sesión: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Autenticación"},
     *     summary="Refrescar token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refrescado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refrescado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function refresh()
    {
        try {
            $result = $this->authService->refreshToken();
            return $this->successResponse($result, 'Token refrescado exitosamente');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error al refrescar el token: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Autenticación"},
     *     summary="Obtener información del usuario actual",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Información del usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Operación exitosa"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="email", type="string", example="juan@ejemplo.com"),
     *                 @OA\Property(property="tipo", type="string", example="estudiante"),
     *                 @OA\Property(property="programa_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function me()
    {
        return $this->successResponse($this->authService->me());
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada correctamente"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function logout()
    {
        $this->authService->logout();
        return $this->successResponse(null, 'Sesión cerrada correctamente');
    }
}
