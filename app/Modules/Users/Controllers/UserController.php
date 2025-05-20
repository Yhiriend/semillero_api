<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Services\UserService;
use App\Modules\Users\Requests\UserStoreRequest;
use App\Modules\Users\Requests\UserUpdateRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Usuarios",
 *     description="Operaciones CRUD para la gestión de usuarios"
 * )
 */
class UserController
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Listar todos los usuarios",
     *     @OA\Response(
     *         response=200,
     *         description="Usuarios obtenidos correctamente"
     *     )
     * )
     */
    public function index()
    {
        try {
            $users = $this->userService->getAllUsers();
            return $this->successResponse($users, 'Usuarios obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los usuarios: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Obtener un usuario por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario obtenido correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            return $this->successResponse($user, 'Usuario obtenido correctamente');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Usuario no encontrado', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el usuario: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Usuarios"},
     *     summary="Crear un nuevo usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado correctamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(UserStoreRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());
            return $this->successResponse($user, 'Usuario creado correctamente', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Actualizar un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(UserUpdateRequest $request, $id)
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());
            return $this->successResponse($user, 'Usuario actualizado correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Usuario no encontrado', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Usuarios"},
     *     summary="Eliminar un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->userService->deleteUser($id);
            return $this->successResponse(null, 'Usuario eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Usuario no encontrado', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
