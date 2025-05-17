<?php

namespace App\Modules\Programs\Services;

use App\Modules\Programs\Repositories\ProgramRepository;

class ProgramService
{
    public function __construct(protected ProgramRepository $programRepository)
    {
    }

    public function getAll()
    {
        try {
            $programs = $this->programRepository->getAll();

            if ($programs->isEmpty()) {
                throw new \Exception('No se encontraron programas inscritos');
            }

            return $programs;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener los programas: ' . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            return $this->programRepository->getById($id);
        } catch (\Exception $e) {
            throw new \Exception("No se encontró el programa con ID: {$id}");
        }
    }

    public function create(array $data)
    {
        try {
            return $this->programRepository->create($data);
        } catch (\Exception $e) {
            throw new \Exception('No se pudo crear el programa: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            $program = $this->programRepository->update($id, $data);

            if (!$program) {
                throw new \Exception("No se encontró el programa con ID: {$id} para actualizar");
            }

            return $program;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar el programa con ID: {$id}: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->programRepository->delete($id);

            if (!$deleted) {
                throw new \Exception("No se encontró el programa con ID: {$id} para eliminar");
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar el programa con ID: {$id}: " . $e->getMessage());
        }
    }
}