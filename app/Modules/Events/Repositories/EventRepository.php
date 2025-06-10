<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Events\Models\EventModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class EventRepository
{
    public function getFilteredEvents(array $filters): LengthAwarePaginator
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;
        
        $query = EventModel::query()
            ->select('id', 'nombre', 'descripcion', 'coordinador_id', 'fecha_inicio', 'fecha_fin', 'ubicacion')
            ->with([
                'coordinador:id,nombre',
                'activities:id,titulo,evento_id',
                'activities.responsables:id,nombre'
            ]);

        // Aplicar filtros
        if (isset($filters['fecha_inicio'])) {
            $query->where('fecha_inicio', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin'])) {
            $query->where('fecha_fin', '<=', $filters['fecha_fin']);
        }

        if (isset($filters['coordinador_id'])) {
            $query->where('coordinador_id', $filters['coordinador_id']);
        }

        // Ordenar por fecha de inicio por defecto
        $query->orderBy('fecha_inicio', 'desc');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findOrFail(int $id): EventModel
    {
        $event = EventModel::with(['coordinador', 'activities.responsables'])->find($id);

        if (!$event) {
            throw new ModelNotFoundException("Evento no encontrado con ID: {$id}");
        }

        return $event;
    }

    public function create(array $data): EventModel
    {
        return EventModel::create($data);
    }

    public function update(EventModel $event, array $data): EventModel
    {
        $event->update($data);
        return $event->fresh();
    }

    public function delete(EventModel $event): void
    {
        $event->delete();
    }

    protected function buildQuery(array $filters): Builder
    {
        $query = EventModel::with(['coordinador', 'activities.responsables']);

        if (isset($filters['fecha_inicio'])) {
            $query->where('fecha_inicio', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin'])) {
            $query->where('fecha_fin', '<=', $filters['fecha_fin']);
        }

        if (isset($filters['coordinador_id'])) {
            $query->where('coordinador_id', $filters['coordinador_id']);
        }

        return $query;
    }
    public function hasConflictCoodinator(int $evaluadorId)
    {
        return DB::table('Usuario')
            ->join('Usuario_Rol', 'Usuario.id', '=', 'Usuario_Rol.usuario_id')
            ->where('Usuario.id', $evaluadorId)
            ->where('Usuario.tipo', 'profesor')
            ->where('Usuario_Rol.rol_id', 5)
            ->exists();
    }

    public function getProjects($name = null)
    {
        return DB::table('proyecto')
            ->select('id', 'titulo')
            ->where(function ($query) use ($name) {
                if ($name) {
                    $query->where('titulo', 'like', '%' . $name . '%');
                }
            })
            ->get();
    }

    public function getCoordinators($name = null)
    {
        return DB::table('usuario')
            ->join('usuario_rol', 'usuario.id', '=', 'usuario_rol.usuario_id')
            ->where('usuario.tipo', 'profesor')
            ->where('usuario_rol.rol_id', 5)
            ->where(function ($query) use ($name) {
                if ($name) {
                    $query->where('usuario.nombre', 'like', '%' . $name . '%');
                }
            })
            ->select('usuario.id', 'usuario.nombre')
            ->get();
    }

    public function getResponsables($name = null)
    {
        return DB::table('usuario')
            ->join('usuario_rol', 'usuario.id', '=', 'usuario_rol.usuario_id')
            ->whereIn('usuario.tipo', ['profesor', 'estudiante']) 
            ->when($name, function ($query, $name) { 
                return $query->where('usuario.nombre', 'like', '%' . $name . '%');
            })
            ->select('usuario.id', 'usuario.nombre')
            ->get();
    }
}