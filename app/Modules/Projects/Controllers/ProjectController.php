<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Projects\Requests\UpdateStatusProjectRequest;
use App\Modules\Projects\Services\ProjectService;
use App\Modules\Projects\Resources\ProjectResource;
use App\Modules\Projects\Models\ProjectUsersModel;
use App\Modules\Projects\Requests\StoreProjectRequest;
use App\Modules\Projects\Requests\StoreProjectUserRequest;
use App\Modules\Projects\Requests\UpdateProjectRequest;
use GuzzleHttp\Psr7\Response;

class ProjectController extends Controller
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
                'data' => ProjectResource::collection($projects)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project by id with university information
     */
    public function getProjectById($id)
    {
        try {
            $project = $this->projectService->getProjectById($id);
            return response()->json([
                'success' => true,
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store project
     */
    public function storeProject(StoreProjectRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $project = $this->projectService->storeProject($validatedData, $id);
            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update project
     */
    public function updateProject(UpdateProjectRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $project = $this->projectService->updateProject($id, $validatedData);
            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Modules\Projects\Requests\UpdateStatusProjectRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(UpdateStatusProjectRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $project = $this->projectService->changeStatus($id, $validatedData['status']);
            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a student to a specific project.
     *
     * This method receives a project ID and validated request data to assign a student
     * to the given project using the project service. It returns a JSON response
     * indicating success or failure.
     *
     * @param int $id The ID of the project to which the student will be assigned.
     * @param StoreProjectUserRequest $request The validated request containing the student's data.
     * @return \Illuminate\Http\JsonResponse JSON response with success status and message.
     */
    public function assignStudentToProject($id, StoreProjectUserRequest $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validated();

            // Call the service method to assign the student to the project
            $this->projectService->assignStudentToProject($id, $validatedData);

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'The student has been successfully assigned to the project.'
            ]);
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
