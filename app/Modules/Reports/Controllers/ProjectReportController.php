<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Reports\Services\ProjectService;
use App\Modules\Reports\Resources\ProjectReportResource;
use App\Modules\Projects\Requests\StoreProjectRequest;
use App\Modules\Projects\Requests\StoreProjectUserRequest;
use App\Modules\Projects\Requests\UpdateProjectRequest;
use GuzzleHttp\Psr7\Response;

class ProjectReportController extends Controller
{
    protected $projectService;

    public function __construct()
    {
        $this->projectService = new ProjectService();
    }

    /**
     * Get all projects
     */
    public function getAllProjects()
    {
        try {
            $projects = $this->projectService->getAllProjects();
            return response()->json([
                'success' => true,
                'data' => ProjectReportResource::collection($projects)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
