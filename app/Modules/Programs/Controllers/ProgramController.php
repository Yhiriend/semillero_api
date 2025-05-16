<?php

namespace App\Modules\Programs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Programs\Services\ProgramService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Programas",
 *     description="Operaciones relacionadas con la gestión de programas"
 * )
 */
class ProgramController extends Controller
{
    use ApiResponse;

    public function __construct(protected ProgramService $programService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/programs",
     *     summary="Listar todos los programas",
     *     description="Obtiene una lista de todos los programas registrados en el sistema. Parte de la gestión de programas. Requiere rol de administrador.",
     *     operationId="getAllPrograms",
     *     tags={"Programas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Programas obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programas obtenidos correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Ingeniería de Sistemas"),
     *                     @OA\Property(property="descripcion", type="string", example="Programa enfocado en el desarrollo de software"),
     *                     @OA\Property(property="facultad_id", type="integer", example=1),
     *                     @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z"),
     *                     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z")
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
     *             @OA\Property(property="message", type="string", example="No se encontraron programas inscritos")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $programs = $this->programService->getAll();
            return $this->successResponse($programs, 'Programas obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/programs/{program}",
     *     summary="Obtener un programa por ID",
     *     description="Devuelve los detalles de un programa específico por su ID. Parte de la gestión de programas. Requiere rol de administrador.",
     *     operationId="getProgramById",
     *     tags={"Programas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="program",
     *         in="path",
     *         description="ID del programa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa obtenido correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Ingeniería de Sistemas"),
     *                 @OA\Property(property="descripcion", type="string", example="Programa enfocado en el desarrollo de software"),
     *                 @OA\Property(property="facultad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z")
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
     *         description="Programa no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el programa con ID: 1")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $program = $this->programService->getById($id);
            return $this->successResponse($program, 'Programa obtenido correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/programs",
     *     summary="Crear un nuevo programa",
     *     description="Crea un nuevo programa con el nombre, descripción y facultad proporcionados. Parte de la gestión de programas. Requiere rol de administrador.",
     *     operationId="createProgram",
     *     tags={"Programas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del programa",
     *         @OA\JsonContent(
     *             required={"nombre", "facultad_id"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Ingeniería de Sistemas", description="Nombre del programa"),
     *             @OA\Property(property="descripcion", type="string", maxLength=1000, example="Programa enfocado en el desarrollo de software", description="Descripción del programa", nullable=true),
     *             @OA\Property(property="facultad_id", type="integer", example=1, description="ID de la facultad a la que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Programa creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa creado correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Ingeniería de Sistemas"),
     *                 @OA\Property(property="descripcion", type="string", example="Programa enfocado en el desarrollo de software"),
     *                 @OA\Property(property="facultad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z")
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
     *                 @OA\Property(property="facultad_id", type="array", @OA\Items(type="string", example="El campo facultad_id debe ser un entero válido"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo crear el programa: Error de base de datos")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'facultad_id' => 'required|integer|exists:Facultad,id',
            ]);
            $program = $this->programService->create($validatedData);
            return $this->successResponse($program, 'Programa creado correctamente', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('No se pudo crear el programa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/programs/{program}",
     *     summary="Actualizar un programa",
     *     description="Actualiza el nombre, descripción y/o facultad de un programa existente por su ID. Parte de la gestión de programas. Requiere rol de administrador.",
     *     operationId="updateProgram",
     *     tags={"Programas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="program",
     *         in="path",
     *         description="ID del programa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos actualizados del programa",
     *         @OA\JsonContent(
     *             required={"nombre", "facultad_id"},
     *             @OA\Property(property="nombre", type="string", maxLength=255, example="Ingeniería de Sistemas Actualizada", description="Nuevo nombre del programa"),
     *             @OA\Property(property="descripcion", type="string", maxLength=1000, example="Programa actualizado para desarrollo de software", description="Nueva descripción del programa", nullable=true),
     *             @OA\Property(property="facultad_id", type="integer", example=1, description="ID de la facultad a la que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa actualizado correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Ingeniería de Sistemas Actualizada"),
     *                 @OA\Property(property="descripcion", type="string", example="Programa actualizado para desarrollo de software"),
     *                 @OA\Property(property="facultad_id", type="integer", example=1),
     *                 @OA\Property(property="fecha_creacion", type="string", format="date-time", example="2025-05-16T10:20:00.000000Z"),
     *                 @OA\Property(property="fecha_actualizacion", type="string", format="date-time", example="2025-05-16T10:21:00.000000Z")
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
     *         description="Programa no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el programa con ID: 1")
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
     *                 @OA\Property(
     *                     property="nombre",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo nombre es obligatorio")
     *                 ),
     *                 @OA\Property(
     *                     property="facultad_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo facultad_id debe ser un entero válido")
     *                 )
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
                'descripcion' => 'nullable|string|max:1000',
                'facultad_id' => 'required|integer|exists:Facultad,id',
            ]);
            $program = $this->programService->update($id, $validatedData);
            return $this->successResponse($program, 'Programa actualizado correctamente');
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/programs/{program}",
     *     summary="Eliminar un programa",
     *     description="Elimina un programa existente por su ID. Parte de la gestión de programas. Requiere rol de administrador.",
     *     operationId="deleteProgram",
     *     tags={"Programas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="program",
     *         in="path",
     *         description="ID del programa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programa eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programa eliminado correctamente"),
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
     *         description="Programa no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se encontró el programa con ID: 1")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->programService->delete($id);
            return $this->successResponse(null, 'Programa eliminado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}