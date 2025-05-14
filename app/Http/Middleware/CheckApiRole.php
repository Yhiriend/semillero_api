<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckApiRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->jsonResponse(401, 'Usuario no autenticado');
            }

            $processedRoles = $this->parseRoles($roles);

            if (empty($processedRoles)) {
                return $this->jsonResponse(403, 'No se especificaron roles requeridos');
            }

            // Verifica si el usuario tiene al menos uno de los roles requeridos
            if ($user->hasAnyRole($processedRoles)) {
                return $next($request);
            }

            return $this->jsonResponse(403, 'Acceso denegado: Rol no autorizado', [
                'required_roles' => $processedRoles,
                'user_roles' => $user->roles->pluck('nombre')
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(401, 'Error de autenticación', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function parseRoles(array $roles): array
    {
        $result = [];

        foreach ($roles as $role) {
            // Soporte para cadenas separadas por comas y eliminando comillas
            foreach (explode(',', $role) as $r) {
                $cleanRole = trim($r, " \"'");
                if (!empty($cleanRole)) {
                    $result[] = $cleanRole;
                }
            }
        }

        return $result;
    }

    protected function jsonResponse(int $status, string $message, array $data = []): Response
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message
        ], $data), $status);
    }
}
