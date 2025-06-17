<?php

namespace App\Modules\Seedbeds\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seedbeds\Requests\StoreSeedbedRequest;
use App\Modules\Seedbeds\Requests\UpdateSeedbedRequest;
use App\Modules\Seedbeds\Services\SeedbedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Programs\Models\ProgramModel;
use App\Modules\Users\Models\UserModel;


/**
 * @OA\Tag(
 *     name="CRUD Semilleros",
 *     description="Operaciones relacionadas con el CRUD de semilleros"
 * )
 */
class SeedbedsController extends Controller
{
    private SeedbedService $seedbedService;

    public function __construct(SeedbedService $seedbedService)
    {
        $this->seedbedService = $seedbedService;
    }

    /**
     * @OA\Get(
     *     path="/api/seedbeds",
     *     summary="Listar semilleros",
     *     tags={"CRUD Semilleros"},
     *     @OA\Response(response=200, description="Lista de semilleros")
     * )
     */

    public function index(Request $request): JsonResponse
    {
        return $this->seedbedService->index($request);
    }

    /**
     * @OA\Get(
     *     path="/api/seedbeds/{id}",
     *     summary="Consultar semillero por ID",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Datos del semillero"),
     *     @OA\Response(response=404, description="Semillero no encontrado")
     * )
     */
    public function show($id): JsonResponse
    {
        return $this->seedbedService->show($id);
    }

    /**
     * @OA\Post(
     *     path="/api/seedbeds",
     *     summary="Crear un nuevo semillero",
     *     tags={"CRUD Semilleros"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion", "programa_id", "profesor_id"},
     *             @OA\Property(property="nombre", type="string", example="Semillero TIC"),
     *             @OA\Property(property="descripcion", type="string", example="Grupo de investigación en tecnología"),
     *             @OA\Property(property="programa_id", type="integer", example=2),
     *             @OA\Property(property="profesor_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Semillero creado correctamente")
     * )
     */
    public function store(StoreSeedbedRequest $request): JsonResponse
    {
        return $this->seedbedService->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/seedbeds/{id}",
     *     summary="Actualizar un semillero existente",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion", "programa_id", "profesor_id"},
     *             @OA\Property(property="nombre", type="string", example="Semillero Redes"),
     *             @OA\Property(property="descripcion", type="string", example="Nuevo enfoque en redes y protocolos"),
     *             @OA\Property(property="programa_id", type="integer", example=1),
     *             @OA\Property(property="profesor_id", type="integer", example=8)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Semillero actualizado"),
     *     @OA\Response(response=404, description="Semillero no encontrado")
     * )
     */
    public function update(UpdateSeedbedRequest $request, $id): JsonResponse
    {
        return $this->seedbedService->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/seedbeds/{id}",
     *     summary="Eliminar un semillero",
     *     tags={"CRUD Semilleros"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Semillero eliminado correctamente"),
     *     @OA\Response(response=404, description="Semillero no encontrado")
     * )
     */
    public function delete($id): JsonResponse
    {
        return $this->seedbedService->delete($id);
    }

    public function coordinators()
    {
        $coordinators = UserModel::where('tipo', 'profesor')->get();
        return response()->json(['data' => $coordinators]);
    }

    public function programs()
    {
        $programs = ProgramModel::all();
        return response()->json(['data' => $programs]);
    }
}
