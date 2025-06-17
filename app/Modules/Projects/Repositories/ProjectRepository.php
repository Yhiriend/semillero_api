<?php

namespace App\Modules\Projects\Repositories;

use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Projects\Models\UserModel;
use Illuminate\Support\Facades\DB;

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
    public function getAllProjects($perPage = 5)
    {
        return $this->projectModel->with('seedbed')->orderBy('fecha_creacion', 'desc')->paginate($perPage);
    }

    /**
     * Get project by id with university information
     */
    public function getProjectById($projectId)
    {
        return $this->projectModel
            ->with('university')
            ->with('seedbed')
            ->findOrFail($projectId);
    }

    /**
     * Store project
     */
    public function storeProject($data, $userId)
    {
        $project = $this->projectModel->create($data);

        // Attach the user as creator to the proyecto_usuario table with assignment date
        $project->students()->attach($userId, [
            'fecha_asignacion' => now()
        ]);

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

    /**
     * Get users assigned to a specific project
     */
    public function getProjectUsers($projectId)
    {
        $project = $this->projectModel->findOrFail($projectId);
        return $project->students()->select('usuario.id', 'usuario.nombre', 'usuario.email')->get();
    }

    /**
     * Get users with role "Integrante Semillero" for a specific seedbed
     */
    public function getSeedbedIntegrantes($seedbedId)
    {
        // Obtén el ID del rol "Integrante Semillero"
        $roleId = DB::table('rol')->where('nombre', 'Integrante Semillero')->value('id');
        if (!$roleId) {
            throw new \Exception('Rol "Integrante Semillero" no encontrado.');
        }

        // Obtén los IDs de usuarios que pertenecen al semillero
        $userIds = DB::table('semillero_usuario')
            ->where('semillero_id', $seedbedId)
            ->pluck('usuario_id');

        // Obtén los IDs de usuarios que ya están asignados a un proyecto
        $assignedUserIds = DB::table('proyecto_usuario')
            ->pluck('usuario_id');

        // Filtra los usuarios que tienen el rol "Integrante Semillero" y NO están en proyecto_usuario
        $users = $this->userModel
            ->whereIn('id', $userIds)
            ->whereNotIn('id', $assignedUserIds)
            ->whereHas('roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            })
            ->with(['roles' => function ($q) use ($roleId) {
                $q->where('id', $roleId);
            }])
            ->select('id', 'nombre', 'email')
            ->get();

        // Mapea para mostrar solo el nombre del rol junto con los datos del usuario
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->roles->first() ? $user->roles->first()->nombre : null
            ];
        });
    }
    public function getProjectEvaluation($projectId)
    {
        return DB::table('evaluacion')->where('proyecto_id', $projectId)->first();
    }

    /**
     * Get seedbed leaders
     */
    public function getSeedbedLideres($seedbedId)
    {
        // Obtén el ID del rol "Líder de Proyecto"
        $roleId = DB::table('rol')->where('nombre', 'Lider de Proyecto')->value('id');
        if (!$roleId) {
            throw new \Exception('Rol "Líder de Proyecto" no encontrado.');
        }

        // Obtén los IDs de usuarios que pertenecen al semillero
        $userIds = DB::table('semillero_usuario')
            ->where('semillero_id', $seedbedId)
            ->pluck('usuario_id');

        // Filtra los usuarios que tienen el rol "Líder de Proyecto"
        $users = $this->userModel
            ->whereIn('id', $userIds)
            ->whereHas('roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            })
            ->with(['roles' => function ($q) use ($roleId) {
                $q->where('id', $roleId);
            }])
            ->select('id', 'nombre', 'email')
            ->get();

        // Mapea para mostrar solo el nombre del rol junto con los datos del usuario
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->roles->first() ? $user->roles->first()->nombre : null
            ];
        });
    }

    /**
     * Get coordinators of a seedbed
     */
    public function getSeedbedCoordinadores($seedbedId)
    {
        // Obtener el programa_id del semillero
        $programaId = DB::table('semillero')->where('id', $seedbedId)->value('programa_id');
        if (!$programaId) {
            throw new \Exception('Semillero no encontrado o sin programa asociado.');
        }

        // Obtener el ID del rol "Coordinador de Proyecto"
        $roleId = DB::table('rol')->where('nombre', 'Coordinador de Proyecto')->value('id');
        if (!$roleId) {
            throw new \Exception('Rol "Coordinador de Proyecto" no encontrado.');
        }

        // Buscar usuarios con ese rol y programa
        $users = $this->userModel
            ->where('programa_id', $programaId)
            ->whereHas('roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            })
            ->with(['roles' => function ($q) use ($roleId) {
                $q->where('id', $roleId);
            }])
            ->select('id', 'nombre', 'email')
            ->get();

        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->roles->first() ? $user->roles->first()->nombre : null
            ];
        });
    }
}
