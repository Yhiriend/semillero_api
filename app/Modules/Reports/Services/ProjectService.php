<?php

namespace App\Modules\Reports\Services;
use App\Modules\Projects\Models\ProjectUsersModel;

use App\Modules\Reports\Repositories\ProgramReportRepository;

class ProjectService
{
    protected $projectRepository;
    
    public function __construct()
    {
        $this->projectRepository = new ProgramReportRepository();
    }

    /**
     * Get all projects
     */
    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }


}
?>