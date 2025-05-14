<?php

namespace App\Modules\GestionDeSemilleros\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionDeSemilleros\Models\Semillero;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SemilleroApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/semilleros",
     *     summary="Listar todos los semilleros",
     *     tags={"CRUD Semilleros"},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de semilleros"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Semillero::with(['coordinador', 'programa']);

        if ($request->has('query') && $request->query('query') !== null) {
            $busqueda = $request->query('query');

            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%$busqueda%")
                    ->orWhere('id', $busqueda);
            });
        }

        $semilleros = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $semilleros
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/semilleros/{id}",
     *     summary="Consultar un semillero por ID",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del semillero",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Semillero encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Semillero no encontrado"
     *     )
     * )
     */


    public function show($id): JsonResponse
    {
        $semillero = Semillero::with(['coordinador', 'programa'])->find($id);

        if (!$semillero) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semillero no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $semillero
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/semilleros",
     *     summary="Crear un nuevo semillero",
     *     tags={"CRUD Semilleros"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion", "programa_id", "profesor_id"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="programa_id", type="integer"),
     *             @OA\Property(property="profesor_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Semillero creado correctamente"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'required|string',
            'programa_id' => 'required|exists:programa,id',
            'profesor_id' => 'required|exists:usuario,id',
        ]);

        $semillero = Semillero::create([
            'nombre'              => $validated['nombre'],
            'descripcion'         => $validated['descripcion'],
            'programa_id'         => $validated['programa_id'],
            'coordinador_id'      => $validated['profesor_id'],
            'fecha_creacion'      => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'message'    => 'Semillero creado correctamente',
            'semillero'  => $semillero
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/semilleros/{id}",
     *     summary="Actualizar un semillero por ID",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del semillero",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion", "programa_id", "profesor_id"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="programa_id", type="integer"),
     *             @OA\Property(property="profesor_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Semillero actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Semillero no encontrado"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'required|string',
            'programa_id' => 'required|exists:programa,id',
            'profesor_id' => 'required|exists:usuario,id',
        ]);

        $semillero = Semillero::find($id);

        if (!$semillero) {
            return response()->json(['message' => 'Semillero no encontrado'], 404);
        }

        $semillero->update([
            'nombre'              => $validated['nombre'],
            'descripcion'         => $validated['descripcion'],
            'programa_id'         => $validated['programa_id'],
            'coordinador_id'      => $validated['profesor_id'],
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'message'   => 'Semillero actualizado correctamente',
            'semillero' => $semillero,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/semilleros/{id}",
     *     summary="Eliminar un semillero por ID",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del semillero",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Semillero eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Semillero no encontrado"
     *     )
     * )
     */
    public function delete($id): JsonResponse
    {
        $semillero = Semillero::find($id);

        if (!$semillero) {
            return response()->json(['message' => 'Semillero no encontrado'], 404);
        }

        $semillero->delete();
        return response()->json(['message' => 'Semillero eliminado correctamente'], 200);
    }


}
