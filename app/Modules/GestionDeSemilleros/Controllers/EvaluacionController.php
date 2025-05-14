<?php

namespace App\Modules\GestionDeSemilleros\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\GestionDeSemilleros\Models\Evaluacion;
use Illuminate\Http\JsonResponse;

class EvaluacionController extends Controller
{
    /**
     * Registra (o actualiza) una evaluación.
     * POST /api/evaluaciones
     */
    public function registrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'proyecto_id'               => 'required|exists:proyecto,id',
            'evaluador_id'              => 'required|exists:usuario,id',
            'dominio_tema'              => 'required|integer|between:1,5',
            'manejo_auditorio'          => 'required|integer|between:1,5',
            'planteamiento_problema'    => 'required|integer|between:1,5',
            'justificacion'             => 'required|integer|between:1,5',
            'objetivo_general'          => 'required|integer|between:1,5',
            'objetivo_especifico'       => 'required|integer|between:1,5',
            'marco_teorico'             => 'required|integer|between:1,5',
            'metodologia'               => 'required|integer|between:1,5',
            'resultado_esperado'        => 'required|integer|between:1,5',
            'referencia_bibliografica'  => 'required|integer|between:1,5',
            'comentarios'               => 'nullable|string',
            'estado'                    => 'nullable|string|in:pendiente,completada',
        ]);

        // Crea o actualiza según convenga (aquí siempre crea)
        $evaluacion = Evaluacion::create($data);

        return response()->json([
            'status'  => true,
            'data'    => $evaluacion,
            'message' => 'Evaluación registrada correctamente'
        ], 201);
    }

    /**
     * Lista las evaluaciones de un proyecto.
     * GET /api/evaluaciones?proyecto_id=X
     */
    public function listar(Request $request): JsonResponse
    {
        $params = $request->validate([
            'proyecto_id' => 'required|exists:proyecto,id',
        ]);

        $evaluaciones = Evaluacion::with('evaluador')
            ->where('proyecto_id', $params['proyecto_id'])
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $evaluaciones
        ], 200);
    }
}
