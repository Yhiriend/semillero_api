<?php

namespace App\Modules\Projects\Repositories;

use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Projects\Models\UserModel;

class ProjectRepository
{
    protected $projectModel;
    protected $userModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get all projects
     */
    public function getAllProjects()
    {
        return $this->projectModel->with('hotbet')->get();
    }

    /**
     * Get project by id with university information
     */
    public function getProjectById($projectId)
    {
        return $this->projectModel
            ->with('university') 
            ->with('hotbet')
            ->findOrFail($projectId);
    }

    /**
     * Store project
     */
    public function storeProject($data, $userId)
    {
        $project = $this->projectModel->create($data);
        
        // Attach the user as creator to the proyecto_usuario table
        $project->students()->attach($userId);
        
        return $project;
    }

    /**
     * Update project
     */
    public function updateProject($projectId, $data)
    {
        $project = $this->projectModel->findOrFail($projectId);
        $project->update($data);
        return $project;
    }

    /**
     * Change project status
     */
    public function changeStatus($projectId, $newStatus)
    {
        $project = $this->projectModel->findOrFail($projectId);
        $project->estado = $newStatus;
        $project->save();
        return $project;
    }

    /**
     * Assign student to project
     */
    public function assignStudentToProject($projectId, $userId)
    {
        $project = $this->projectModel->findOrFail($projectId);
        $user = $this->userModel->findOrFail($userId);

        // Verificamos que la relación no exista previamente
        if (!$project->students()->where('usuario_id', $userId)->exists()) {
            // Asociamos el estudiante al proyecto con la fecha de asignación actual
            $project->students()->attach($userId, [
                'fecha_asignacion' => now()
            ]);
        } else {
            // Opcional: lanzar una excepción o manejar el caso en que ya esté asignado
            throw new \Exception("El estudiante ya está asignado a este proyecto.");
        }
    }
}
