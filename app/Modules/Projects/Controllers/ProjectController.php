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
use App\Modules\Projects\Models\UserModel; // o el namespace correcto de tu modelo User
use App\Modules\Projects\Models\HotbedModel; // ajusta el namespace si es necesario
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    protected $projectService;
    protected $projectRepository;

    public function __construct()
    {
        $this->projectService = new ProjectService();
        $this->projectRepository = app()->make('App\Modules\Projects\Repositories\ProjectRepository');
    }

    /**
     * Get all projects
     */
    public function getAllProjects(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 5); // Default to 10 if not provided
            $perPage = is_numeric($perPage) ? (int)$perPage : 5; // Ensure per_page is an integer
            $projects = $this->projectService->getAllProjects($perPage);
            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                ]
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

    public function getProjectUsers($id)
    {
        try {
            $users = $this->projectService->getProjectUsers($id);
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudieron obtener los usuarios del proyecto.'], 500);
        }
    }

    public function getSeedbedIntegrantes($seedbedId)
    {
        try {
            $users = $this->projectRepository->getSeedbedIntegrantes($seedbedId);

            // Puedes agregar el nombre del semillero si lo necesitas
            $semillero = DB::table('semillero')->where('id', $seedbedId)->first();

            return response()->json([
                'success' => true,
                'semillero' => $semillero ? $semillero->nombre : null,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProjectEvaluation($id)
    {
        try {
            // Puedes crear un método en el servicio o repositorio para obtener la evaluación
            $evaluation = DB::table('evaluacion')->where('proyecto_id', $id)->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró evaluación para este proyecto.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $evaluation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSeedbedLideres($seedbedId)
    {
        try {
            $lideres = $this->projectRepository->getSeedbedLideres($seedbedId);
            return response()->json($lideres);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSeedbedCoordinadores($seedbedId)
    {
        try {
            $coordinadores = $this->projectService->getSeedbedCoordinadores($seedbedId);
            return response()->json($coordinadores);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get seedbeds where a leader is registered
     *
     * @param int $leaderId The ID of the leader user
     * @return \Illuminate\Http\JsonResponse JSON response with seedbeds data
     */
    public function getSeedbedProjects($leaderId)
    {
        try {
            // Query the database directly to get seedbeds where the leader is registered
            $seedbeds = DB::table('semillero')
                ->join('semillero_usuario', 'semillero.id', '=', 'semillero_usuario.semillero_id')
                ->where('semillero_usuario.usuario_id', $leaderId)
                ->select('semillero.*')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $seedbeds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
