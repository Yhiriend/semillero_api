<?php

namespace App\Modules\Seedbeds\Services;

use App\Modules\Seedbeds\Repositories\SeedbedRepository;
use App\Modules\Seedbeds\Requests\StoreSeedbedRequest;
use App\Modules\Seedbeds\Requests\UpdateSeedbedRequest;
use App\Modules\Seedbeds\Resources\SeedbedResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeedbedService
{
    protected SeedbedRepository $repository;

    public function __construct(SeedbedRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->getAll($request->query('query'));

        return response()->json([
            'status' => 'success',
            'data' => SeedbedResource::collection($data)
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $seedbed = $this->repository->findById($id);

        if (!$seedbed) {
            return response()->json(['status' => 'error', 'message' => 'Semillero no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new SeedbedResource($seedbed)
        ]);
    }

    public function store(StoreSeedbedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $created = $this->repository->create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'programa_id' => $data['programa_id'],
            'coordinador_id' => $data['profesor_id'],
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'message'   => 'Semillero creado correctamente',
            'semillero' => new SeedbedResource($created)
        ], 201);
    }

    public function update(UpdateSeedbedRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $seedbed = $this->repository->findRaw($id);

        if (!$seedbed) {
            return response()->json(['message' => 'Semillero no encontrado'], 404);
        }

        $this->repository->update($seedbed, [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'programa_id' => $data['programa_id'],
            'coordinador_id' => $data['profesor_id'],
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'message'   => 'Semillero actualizado correctamente',
            'semillero' => new SeedbedResource($seedbed)
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $seedbed = $this->repository->findRaw($id);

        if (!$seedbed) {
            return response()->json(['message' => 'Semillero no encontrado'], 404);
        }

        $this->repository->delete($seedbed);

        return response()->json(['message' => 'Semillero eliminado correctamente']);
    }
}
