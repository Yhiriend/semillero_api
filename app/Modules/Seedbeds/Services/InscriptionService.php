<?php

namespace App\Modules\Seedbeds\Services;

use Illuminate\Http\JsonResponse;
use App\Modules\Seedbeds\Repositories\InscriptionRepository;
use App\Modules\Seedbeds\Requests\StoreInscriptionRequest;
use App\Modules\Seedbeds\Requests\FilterInscriptionRequest;
use App\Modules\Seedbeds\Resources\InscriptionResource;

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


    public function index(FilterInscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $inscriptions = $this->repository->findByFilters(
            $data['semillero_id'] ?? null,
            $data['usuario_id'] ?? null
        );

        if ($inscriptions->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron inscripciones.'
            ], 404);
        }

        return response()->json([
            'data' => InscriptionResource::collection($inscriptions)
        ]);
    }
}
