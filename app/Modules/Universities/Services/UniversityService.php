<?php

namespace App\Modules\Universities\Services;

use App\Modules\Universities\Repositories\UniversityRepository;

class UniversityService
{
    public function __construct(protected UniversityRepository $universityRepository)
    {
    }

    public function getAll()
    {
        try {
            $universities = $this->universityRepository->getAll();

            if ($universities->isEmpty()) {
                throw new \Exception('No se encontraron universidades inscritas');
            }

            return $universities;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener las universidades: ' . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            return $this->universityRepository->getById($id);
        } catch (\Exception $e) {
            throw new \Exception("No se encontró la universidad con ID: {$id}");
        }
    }

    public function create(array $data)
    {
        try {
            return $this->universityRepository->create($data);
        } catch (\Exception $e) {
            throw new \Exception('No se pudo crear la universidad: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            $university = $this->universityRepository->update($id, $data);

            if (!$university) {
                throw new \Exception("No se encontró la universidad con ID: {$id} para actualizar");
            }

            return $university;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar la universidad con ID: {$id}: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->universityRepository->delete($id);

            if (!$deleted) {
                throw new \Exception("No se encontró la universidad con ID: {$id} para eliminar");
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar la universidad con ID: {$id}: " . $e->getMessage());
        }
    }
}