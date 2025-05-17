<?php

namespace App\Modules\Projects\Services;
use App\Modules\Projects\Models\ProjectUsersModel;

use App\Modules\Projects\Repositories\ProjectRepository;

class ProjectService
{
    protected $projectRepository;
    
    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
    }

    /**
     * Get all projects
     */
    public function getAllProjects()
    {
        return $this->projectRepository->getAllProjects();
    }

    /**
     * Get project by id with university information
     */
    public function getProjectById($projectId)
    {
        return $this->projectRepository->getProjectById($projectId);
    }

    /**
     * Store project
     */
    public function storeProject($data, $userId)
    {
        return $this->projectRepository->storeProject($data, $userId);
    }

    /**
     * Update project
     */
    public function updateProject($projectId, $data)
    {
        return $this->projectRepository->updateProject($projectId, $data);
    }
    
    /**
     * Change project status
     */
    public function changeStatus($projectId, $newStatus)
    {
        return $this->projectRepository->changeStatus($projectId, $newStatus);
    }

    public function assignStudentToProject($projectId, $userId)
    {
        // Aquí puedes agregar lógica para evitar duplicados si deseas
        return $this->projectRepository->assignStudentToProject($projectId, $userId);
    }
}
?>