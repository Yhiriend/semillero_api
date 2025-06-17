<?php

namespace App\Modules\Seedbeds\Services;

use App\Modules\Seedbeds\Repositories\SeedbedRepository;
use App\Modules\Seedbeds\Requests\StoreSeedbedRequest;
use App\Modules\Seedbeds\Requests\UpdateSeedbedRequest;
use App\Modules\Seedbeds\Resources\SeedbedResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


use App\Modules\Programs\Services\ProgramService;
use App\Modules\Users\Services\UserService;
use App\Modules\Seedbeds\Models\Seedbed;

class SeedbedService
{
    protected SeedbedRepository $repository;
    protected ProgramService $programService;
    protected UserService $userService;
    public function __construct(SeedbedRepository $repository, ProgramService $programService, UserService $userService)
    {
        $this->repository = $repository;
        $this->programService = $programService;
        $this->userService = $userService;
    }

    //!Cambio
    public function index(Request $request)
    {
        try {
            $query = Seedbed::query();

            if ($search = $request->get('q')) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('id', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%")
                    ->orWhereHas('programa', function ($programQuery) use ($search) {
                        $programQuery->where('nombre', 'like', "%{$search}%");
                    });
            }
            $seedbeds = $query->orderBy('fecha_creacion', 'desc')->paginate(7);

            $seedbeds->getCollection()->transform(function ($seedbed) {
                // Coordinador nombre
                $coordinador = $this->userService->getUserById($seedbed->coordinador_id);
                $seedbed->coordinador = $coordinador->nombre;

                //Programa nombre
                $programa = $this->programService->getById($seedbed->programa_id);
                $seedbed->programa = $programa->nombre;

                return $seedbed;
            });
            return response()->json([
                'data' => $seedbeds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los semilleros: ' . $e->getMessage()
            ], 500);
        }
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
