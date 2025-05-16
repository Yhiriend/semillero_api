<?php

namespace App\Modules\Faculties\Services;

use App\Modules\Faculties\Repositories\FacultyRepository;

class FacultyService
{
    public function __construct(protected FacultyRepository $facultyRepository)
    {
    }

    public function getAll()
    {
        try {
            $faculties = $this->facultyRepository->getAll();

            if ($faculties->isEmpty()) {
                throw new \Exception('No se encontraron facultades inscritas');
            }

            return $faculties;
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener las facultades: ' . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            return $this->facultyRepository->getById($id);
        } catch (\Exception $e) {
            throw new \Exception("No se encontró la facultad con ID: {$id}");
        }
    }

    public function create(array $data)
    {
        try {
            return $this->facultyRepository->create($data);
        } catch (\Exception $e) {
            throw new \Exception('No se pudo crear la facultad: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            $faculty = $this->facultyRepository->update($id, $data);

            if (!$faculty) {
                throw new \Exception("No se encontró la facultad con ID: {$id} para actualizar");
            }

            return $faculty;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar la facultad con ID: {$id}: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->facultyRepository->delete($id);

            if (!$deleted) {
                throw new \Exception("No se encontró la facultad con ID: {$id} para eliminar");
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar la facultad con ID: {$id}: " . $e->getMessage());
        }
    }
}