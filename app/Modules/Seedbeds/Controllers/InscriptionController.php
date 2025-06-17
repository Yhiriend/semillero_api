<?php

namespace App\Modules\Seedbeds\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Seedbeds\Services\InscriptionService;
use App\Modules\Seedbeds\Requests\StoreInscriptionRequest;
use App\Modules\Seedbeds\Requests\FilterInscriptionRequest;
use App\Modules\Seedbeds\Models\Seedbed;
use App\Modules\Users\Models\UserModel;
use Illuminate\Support\Facades\DB;
use App\Modules\Seedbeds\Models\Inscription;
use App\Modules\Seedbeds\Resources\StudentResource;
class InscriptionController extends Controller
{
    protected InscriptionService $inscriptionService;

    public function __construct(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }

    /**
     * @OA\Post(
     *     path="/api/inscriptions",
     *     summary="Inscribir estudiante en un semillero",
     *     tags={"Inscripciones"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"semillero_id", "usuario_id"},
     *             @OA\Property(property="semillero_id", type="integer", example=1),
     *             @OA\Property(property="usuario_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscripción exitosa"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Ya existe inscripción"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     )
     * )
     */
    public function store(StoreInscriptionRequest $request)
    {
        return $this->inscriptionService->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/inscriptions",
     *     summary="Listar inscripciones",
     *     tags={"Inscripciones"},
     *     @OA\Parameter(
     *         name="semillero_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="usuario_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de inscripciones"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron inscripciones"
     *     )
     * )
     */
    public function index(FilterInscriptionRequest $request)
    {
        return $this->inscriptionService->index($request);
    }

    public function students()
    {
        $students = UserModel::where('tipo', 'estudiante')->get();

        return response()->json(['data' => $students]);
    }

    public function studentsBySeedbed($id)
{
    $students = Inscription::where('semillero_id', $id)
        ->with(['student.programa'])
        ->get()
        ->pluck('student')
        ->filter(); // elimina null por si hay relaciones rotas

    return response()->json(['data' => $students->values()]);
}


    

    public function destroy($semillero_id, $usuario_id)
    {
        $deleted = DB::table('semillero_usuario')
            ->where('semillero_id', $semillero_id)
            ->where('usuario_id', $usuario_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Inscripción eliminada correctamente'], 200);
        } else {
            return response()->json(['message' => 'No se encontró la inscripción'], 404);
        }
    }


}
