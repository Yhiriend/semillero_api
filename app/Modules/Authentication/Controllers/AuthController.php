<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Services\AuthService;
use App\Modules\Users\Models\UserModel;
use App\Traits\ApiResponse;
use App\Enums\ResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Modules\Authentication\Requests\RegisterRequest;
use App\Modules\Authentication\Requests\LoginRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordTokenMail;
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

    /*
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
     *             @OA\Property(property="programa_id", type="integer", example=1),
     *             @OA\Property(property="rol", type="integer", example=1)
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
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();
            $data['name'] = $data['nombre'];
            $data['password'] = $data['contraseña'];
            unset($data['nombre'], $data['contraseña']);
            $rolId = $data['rol'];
            unset($data['rol']);

            $result = null;
            DB::beginTransaction();
            try {
                $result = $this->authService->register($data);
                $userId = $result['user']['id'] ?? ($result['user']->id ?? null);
                if (!$userId) {
                    throw new \Exception('No se pudo obtener el ID del usuario registrado.');
                }
                DB::table('usuario_rol')->insert([
                    'usuario_id' => $userId,
                    'rol_id' => $rolId
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return $this->successResponse($result, ResponseCode::RESOURCE_CREATED, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::REGISTRATION_ERROR, 500);
        }
    }

    /*
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
    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();
            $email = $data['email'];
            $password = $data['contraseña'];
            $result = $this->authService->login($email, $password);
            return $this->successResponse($result, ResponseCode::LOGIN_SUCCESS);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INVALID_CREDENTIALS, 401);
        }
    }

    /*
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
            return $this->successResponse($result, ResponseCode::TOKEN_REFRESHED);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::EXPIRED_TOKEN, 401);
        }
    }

    /*
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
        return $this->successResponse($this->authService->me(), ResponseCode::DATA_LOADED);
    }

    /*
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
        return $this->successResponse(null, ResponseCode::LOGOUT_SUCCESS);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/forgot",
     *     tags={"Autenticación"},
     *     summary="Solicitar recuperación de contraseña",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@ejemplo.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token generado y enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token generado con éxito"),
     *             @OA\Property(property="data", type="object", example={"reset_token": "abc123"})
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuario,email',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
            'email.exists' => 'No se encontró ningún usuario con ese correo.',
        ]);

        $user = UserModel::where('email', $request->email)->first();
        $user->reset_token = Str::random(64);
        $user->reset_token_expire_at = Carbon::now()->addHour();
        $user->save();

        // En producción aquí iría el envío de correo
        Mail::to($user->email)->send(new ResetPasswordTokenMail($user->reset_token, $user->email));

        return $this->successResponse([
            'reset_token' => $user->reset_token
        ], ResponseCode::EMAIL_SENT);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/reset",
     *     tags={"Autenticación"},
     *     summary="Restablecer contraseña con token (requiere sesión activa)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abc123"),
     *             @OA\Property(property="password", type="string", format="password", example="nuevaclave123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="nuevaclave123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contraseña actualizada correctamente."),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token inválido o expirado"
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:usuario,reset_token',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'token.required' => 'El token es obligatorio.',
            'token.exists' => 'El token no es válido o ya expiró.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = UserModel::where('reset_token', $request->token)
            ->where('reset_token_expire_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return $this->errorResponse(ResponseCode::EXPIRED_TOKEN, 400);
        }

        $user->contraseña = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expire_at = null;
        $user->save();

        return $this->successResponse(null, ResponseCode::PASSWORD_RESET);
    }

}
