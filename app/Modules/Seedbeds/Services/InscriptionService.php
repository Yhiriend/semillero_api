<?php

namespace App\Modules\Seedbeds\Services;

use Illuminate\Http\JsonResponse;
use App\Modules\Seedbeds\Repositories\InscriptionRepository;
use App\Modules\Seedbeds\Requests\StoreInscriptionRequest;
use App\Modules\Seedbeds\Requests\FilterInscriptionRequest;
use App\Modules\Seedbeds\Resources\InscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Modules\Seedbeds\Models\Inscription;

class InscriptionService
{
    protected InscriptionRepository $repository;

    public function __construct(InscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(StoreInscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->repository->exists($data['semillero_id'], $data['usuario_id'])) {
            return response()->json([
                'message' => 'El estudiante ya está inscrito en este semillero.'
            ], 409);
        }

        $record = $this->repository->insertAndReturn($data['semillero_id'], $data['usuario_id']);

        return response()->json([
            'message' => 'Inscripción realizada correctamente.',
            'data'    => new InscriptionResource($record)
        ], 201);
    }
    //!Cambio
    public function index(Request $request)
    {
        try {
            $query = DB::table('semillero_usuario')
                ->join('usuario', 'usuario.id', '=', 'semillero_usuario.usuario_id')
                ->join('semillero', 'semillero.id', '=', 'semillero_usuario.semillero_id')
                ->join('programa', 'usuario.programa_id', '=', 'programa.id')
                ->select(
                    'semillero_usuario.*',
                    'usuario.nombre as student_name',
                    'usuario.email as student_email',
                    'programa.nombre as program_name',
                    'semillero.nombre as seedbed_name'
                );

            if ($request->has('semillero_id')) {
                $query->where('semillero_usuario.semillero_id', $request->semillero_id);
            }

            if ($search = $request->get('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('usuario.nombre', 'like', "%$search%")
                        ->orWhere('usuario.email', 'like', "%$search%")
                        ->orWhere('programa.nombre', 'like', "%$search%")
                        ->orWhere('semillero.nombre', 'like', "%$search%");
                });
            }

            $inscriptions = $query->orderBy('semillero_usuario.fecha_inscripcion', 'desc')->paginate(7);

            return response()->json([
                'data' => $inscriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las inscripciones: ' . $e->getMessage()
            ], 500);
        }
    }
}
