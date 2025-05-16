<?php

namespace App\Modules\Universities\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Universities\Services\UniversityService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


/**
 * @OA\Tag(
 *     name="Universidades",
 *     description="Operaciones relacionadas con universidades"
 * )
 */
class UniversityController extends Controller
{
    use ApiResponse;

    public function __construct(protected UniversityService $universityService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/universities",
     *     summary="Obtener todas las universidades",
     *     description="Devuelve una lista de todas las universidades registradas.",
     *     operationId="getAllUniversities",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de universidades obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Universidad Ejemplo"),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z")
     *             )),
     *             @OA\Property(property="message", type="string", example="Universidades obtenidas correctamente"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontraron universidades inscritas"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $universities = $this->universityService->getAll();
            return $this->successResponse($universities, 'Universidades obtenidas correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/universities/{university}",
     *     summary="Obtener una universidad por ID",
     *     description="Devuelve los detalles de una universidad específica.",
     *     operationId="getUniversityById",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="university",
     *         in="path",
     *         description="ID de la universidad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Universidad obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Universidad Ejemplo"),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Universidad obtenida correctamente"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Universidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró la universidad con ID: 1"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $university = $this->universityService->getById($id);
            return $this->successResponse($university, 'Universidad obtenida correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/universities",
     *     summary="Crear una nueva universidad",
     *     description="Crea una nueva universidad con el nombre proporcionado.",
     *     operationId="createUniversity",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la universidad",
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Universidad Ejemplo", description="Nombre de la universidad")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Universidad creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Universidad Ejemplo"),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Universidad creada correctamente"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Errores de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="nombre", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio"))
     *             ),
     *             @OA\Property(property="status", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se pudo crear la universidad: Error de base de datos"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
            $university = $this->universityService->create($validatedData);
            return $this->successResponse($university, 'Universidad creada correctamente', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('No se pudo crear la universidad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/universities/{university}",
     *     summary="Actualizar una universidad",
     *     description="Actualiza el nombre de una universidad existente.",
     *     operationId="updateUniversity",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="university",
     *         in="path",
     *         description="ID de la universidad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos actualizados de la universidad",
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Universidad Actualizada", description="Nuevo nombre de la universidad")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Universidad actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Universidad Actualizada"),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T09:50:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:00:00.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Universidad actualizada correctamente"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Universidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró la universidad con ID: 1"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Errores de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="nombre", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio"))
     *             ),
     *             @OA\Property(property="status", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
            $university = $this->universityService->update($id, $validatedData);
            return $this->successResponse($university, 'Universidad actualizada correctamente');
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/universities/{university}",
     *     summary="Eliminar una universidad",
     *     description="Elimina una universidad existente por su ID.",
     *     operationId="deleteUniversity",
     *     tags={"Universidades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="university",
     *         in="path",
     *         description="ID de la universidad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Universidad eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Universidad eliminada correctamente"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Universidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró la universidad con ID: 1"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->universityService->delete($id);
            return $this->successResponse(null, 'Universidad eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}