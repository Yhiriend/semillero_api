<?php

namespace App\Modules\Faculties\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Faculties\Services\FacultyService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Facultades",
 *     description="Operaciones relacionadas con la gestión de facultades"
 * )
 */
class FacultyController extends Controller
{
    use ApiResponse;

    public function __construct(protected FacultyService $facultyService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/faculties",
     *     summary="Listar todas las facultades",
     *     description="Obtiene una lista de todas las facultades registradas en el sistema. Parte de la gestión de facultades. Requiere rol de administrador.",
     *     operationId="getAllFaculties",
     *     tags={"Facultades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Facultades obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facultades obtenidas correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Facultad de Ingeniería"),
     *                     @OA\Property(property="universidad_id", type="integer", example=1),
     *                     @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z"),
     *                     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontraron facultades inscritas")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $faculties = $this->facultyService->getAll();
            return $this->successResponse($faculties, 'Facultades obtenidas correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/faculties/{faculty}",
     *     summary="Obtener una facultad por ID",
     *     description="Devuelve los detalles de una facultad específica por su ID. Parte de la gestión de facultades. Requiere rol de administrador.",
     *     operationId="getFacultyById",
     *     tags={"Facultades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="faculty",
     *         in="path",
     *         description="ID de la facultad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facultad obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facultad obtenida correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Facultad de Ingeniería"),
     *                 @OA\Property(property="universidad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facultad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró la facultad con ID: 1")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $faculty = $this->facultyService->getById($id);
            return $this->successResponse($faculty, 'Facultad obtenida correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/faculties",
     *     summary="Crear una nueva facultad",
     *     description="Crea una nueva facultad con el nombre y universidad proporcionados. Parte de la gestión de facultades. Requiere rol de administrador.",
     *     operationId="createFaculty",
     *     tags={"Facultades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la facultad",
     *         @OA\JsonContent(
     *             required={"nombre", "universidad_id"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Facultad de Ingeniería", description="Nombre de la facultad"),
     *             @OA\Property(property="universidad_id", type="integer", example=1, description="ID de la universidad a la que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Facultad creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facultad creada correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Facultad de Ingeniería"),
     *                 @OA\Property(property="universidad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Errores de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="nombre", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio")),
     *                 @OA\Property(property="universidad_id", type="array", @OA\Items(type="string", example="El campo universidad_id debe ser un entero válido"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo crear la facultad: Error de base de datos")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'universidad_id' => 'required|integer|exists:Universidad,id',
            ]);
            $faculty = $this->facultyService->create($validatedData);
            return $this->successResponse($faculty, 'Facultad creada correctamente', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('No se pudo crear la facultad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/faculties/{faculty}",
     *     summary="Actualizar una facultad",
     *     description="Actualiza el nombre y/o universidad de una facultad existente por su ID. Parte de la gestión de facultades. Requiere rol de administrador.",
     *     operationId="updateFaculty",
     *     tags={"Facultades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="faculty",
     *         in="path",
     *         description="ID de la facultad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos actualizados de la facultad",
     *         @OA\JsonContent(
     *             required={"nombre", "universidad_id"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Facultad de Ingeniería Actualizada", description="Nuevo nombre de la facultad"),
     *             @OA\Property(property="universidad_id", type="integer", example=1, description="ID de la universidad a la que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facultad actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facultad actualizada correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Facultad de Ingeniería Actualizada"),
     *                 @OA\Property(property="universidad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:15:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:16:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facultad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró la facultad con ID: 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Errores de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="nombre", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio")),
     *                 @OA\Property(property="universidad_id", type="array", @OA\Items(type="string", example="El campo universidad_id debe ser un entero válido"))
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'universidad_id' => 'required|integer|exists:Universidad,id',
            ]);
            $faculty = $this->facultyService->update($id, $validatedData);
            return $this->successResponse($faculty, 'Facultad actualizada correctamente');
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/faculties/{faculty}",
     *     summary="Eliminar una facultad",
     *     description="Elimina una facultad existente por su ID. Parte de la gestión de facultades. Requiere rol de administrador.",
     *     operationId="deleteFaculty",
     *     tags={"Facultades"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="faculty",
     *         in="path",
     *         description="ID de la facultad",
     *         required=true,
         *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facultad eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facultad eliminada correctamente"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado (rol no autorizado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para realizar esta acción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facultad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró la facultad con ID: 1")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->facultyService->delete($id);
            return $this->successResponse(null, 'Facultad eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}