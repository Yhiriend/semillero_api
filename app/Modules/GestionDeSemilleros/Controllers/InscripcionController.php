<?php
namespace App\Modules\GestionDeSemilleros\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/inscripciones",
 *     operationId="storeInscripcion",
 *     tags={"Inscripciones"},
 *     summary="Inscribir estudiante a un semillero",
 *     description="Registra una nueva inscripción de estudiante a semillero",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"semillero_id","usuario_id"},
 *             @OA\Property(property="semillero_id", type="integer", example=1),
 *             @OA\Property(property="usuario_id", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Inscripción exitosa",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Inscripción realizada correctamente.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Conflicto - Ya existe inscripción",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="El estudiante ya está inscrito en este semillero.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Datos no válidos",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function store(request $request)
    {
        try {
            $existe = DB::table('semillero_usuario')
                ->where('semillero_id', $request->semillero_id)
                ->where('usuario_id', $request->usuario_id)
                ->exists();

            if ($existe) {
                return response()->json(
                    ['message' => 'El estudiante ya está inscrito en este semillero.'],
                    409
                );
            }

            DB::table('semillero_usuario')->insert([
                'semillero_id'      => $request->semillero_id,
                'usuario_id'        => $request->usuario_id,
                'fecha_inscripcion' => now(),
            ]);

            return response()->json(
                ['message' => 'Inscripción realizada correctamente.'],
                200
            );

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error interno del servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/inscripciones",
     *     summary="Listar inscripciones a semilleros",
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
     *         description="Lista de inscripciones",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="semillero_id", type="integer"),
     *                 @OA\Property(property="usuario_id", type="integer"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = DB::table('semillero_usuario');

        if ($request->has('semillero_id')) {
            $query->where('semillero_id', $request->semillero_id);
        }

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        $inscripciones = $query->get();

        if ($inscripciones->isEmpty()) {
            return response()->json([
                'message' => 'El estudiante no está inscrito en este semillero.',
            ], 404);
        }

        return response()->json($inscripciones);
    }

}
