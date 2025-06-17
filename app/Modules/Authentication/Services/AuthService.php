<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data): array
    {
        $userData = [
            'nombre' => $data['name'],
            'email' => $data['email'],
            'contraseña' => bcrypt($data['password']),
            'tipo' => $data['tipo'] ?? 'estudiante',
            'programa_id' => $data['programa_id'] ?? null,
        ];

        $user = $this->authRepository->createUser($userData);
        $token = JWTAuth::fromUser($user);

        return compact('user', 'token');
    }

      public function login(string $email, string $password): array
    {
        $user = $this->authRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No existe un usuario con este correo electrónico.'],
            ]);
        }

        if (!Hash::check($password, $user->contraseña)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        $token = JWTAuth::fromUser($user);

        // Load the roles relationship (assuming UserModel has a "roles" relationship)
        $user->load('roles');

        // Format the user data to include roles instead of tipo
        $userData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            // Include roles. Assuming Rol model has id and nombre attributes.
            'roles' => $user->roles->map(function($role) {
                return ['id' => $role->id, 'nombre' => $role->nombre];
            })->toArray(),
            'programa_id' => $user->programa_id, // Keep other relevant fields
            // 'tipo' is removed from the response
        ];

        return [
            'user' => $userData, // Return the formatted user data
            'token' => $token,
        ];
    }


    public function refreshToken(): array
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                throw new TokenInvalidException('No se proporcionó un token');
            }

            if (!JWTAuth::check()) {
                throw new TokenInvalidException('Token inválido');
            }

            $newToken = JWTAuth::refresh($token);

            $user = Auth::user();

            if (!$user) {
                throw new TokenInvalidException('Usuario no encontrado');
            }

            return [
                'user' => $user,
                'token' => $newToken
            ];
        } catch (TokenExpiredException $e) {
            throw ValidationException::withMessages([
                'token' => ['El token ha expirado. Por favor, inicie sesión nuevamente.'],
            ]);
        } catch (TokenInvalidException $e) {
            throw ValidationException::withMessages([
                'token' => ['El token no es válido. Por favor, inicie sesión nuevamente. Detalles: ' . $e->getMessage()],
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'token' => ['Error al refrescar el token: ' . $e->getMessage()],
            ]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function me()
    {
        return Auth::user();
    }
}
