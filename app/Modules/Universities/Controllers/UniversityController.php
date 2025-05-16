<?php

namespace App\Modules\Universities\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Universities\Services\UniversityService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UniversityController extends Controller
{
    use ApiResponse;

    public function __construct(protected UniversityService $universityService)
    {
    }

    public function index()
    {
        try {
            $universities = $this->universityService->getAll();
            return $this->successResponse($universities, 'Universidades obtenidas correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $university = $this->universityService->getById($id);
            return $this->successResponse($university, 'Universidad obtenida correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
            $university = $this->universityService->create($validatedData);
            return $this->successResponse($university, 'Universidad creada correctamente', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('No se pudo crear la universidad: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
            $university = $this->universityService->update($id, $validatedData);
            return $this->successResponse($university, 'Universidad actualizada correctamente');
        } catch (ValidationException $e) {
            return $this->errorResponse('Errores de validación', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->universityService->delete($id);
            return $this->successResponse(null, 'Universidad eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}