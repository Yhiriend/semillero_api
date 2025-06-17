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
    public function getAllProjects($perPage = 5)
    {
        return $this->projectRepository->getAllProjects($perPage);
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

    public function getProjectUsers($projectId)
    {
        return $this->projectRepository->getProjectUsers($projectId);
    }

    public function getProjectEvaluation($projectId)
    {
        return $this->projectRepository->getProjectEvaluation($projectId);
    }
    public function getSeedbedIntegrantes($seedbedId)
    {
        return $this->projectRepository->getSeedbedIntegrantes($seedbedId);
    }

    public function getSeedbedLideres($seedbedId)
    {
        return $this->projectRepository->getSeedbedLideres($seedbedId);
    }

    /**
     * Get coordinators of a seedbed
     */
    public function getSeedbedCoordinadores($seedbedId)
    {
        return $this->projectRepository->getSeedbedCoordinadores($seedbedId);
    }
}
