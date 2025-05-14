<?php

namespace App\Modules\GestionDeSemilleros\Controllers;

use Illuminate\Http\Request;
use App\Modules\GestionDeSemilleros\Models\Actividad;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;


class ActividadController extends Controller
{
    /**
     * Registra una nueva actividad.
     * POST /api/actividades
     */
    public function registrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo'        => 'required|string|max:255',
            'descripcion'   => 'required|string',
            'semillero_id'  => 'required|exists:semillero,id',
            'proyecto_id'   => 'nullable|exists:proyecto,id',
            'evento_id'     => 'nullable|exists:evento,id',
            'fecha_inicio'  => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'fecha_fin'     => 'required|date_format:Y-m-d H:i:s|after_or_equal:fecha_inicio',
            'estado'        => 'required|string|in:pendiente,completada',
        ]);

        $actividad = Actividad::create($data);

        return response()->json([
            'status'  => true,
            'data'    => $actividad,
            'message' => 'Actividad registrada correctamente'
        ], 201);
    }

    /**
     * Lista las actividades de un semillero.
     * GET /api/actividades?semillero_id=X
     */
    public function listar(Request $request): JsonResponse
    {
        $params = $request->validate([
            'semillero_id' => 'required|exists:semillero,id',
        ]);

        $actividades = Actividad::where('semillero_id', $params['semillero_id'])
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $actividades
        ], 200);
    }
}
