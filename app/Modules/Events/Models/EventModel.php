<?php

namespace App\Modules\Events\Models;

use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Users\Models\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventModel extends Model
{
    protected $table = 'evento';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
        'coordinador_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime'
    ];

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(ProjectModel::class, 'proyecto_evento', 'evento_id', 'proyecto_id')
            ->withPivot('observaciones', 'fecha_inscripcion');
    }

    public function registeredUsers()
    {
        return UserModel::select([
                'usuario.*',
                'proyecto.id as proyecto_id',
                'proyecto.titulo as proyecto_titulo',
                'proyecto_evento.fecha_inscripcion',
                'semillero.id as semillero_id',
                'semillero.nombre as semillero_nombre'
            ])
            ->join('proyecto_usuario', 'usuario.id', '=', 'proyecto_usuario.usuario_id')
            ->join('proyecto', 'proyecto_usuario.proyecto_id', '=', 'proyecto.id')
            ->join('semillero', 'proyecto.semillero_id', '=', 'semillero.id')
            ->join('proyecto_evento', 'proyecto.id', '=', 'proyecto_evento.proyecto_id')
            ->where('proyecto_evento.evento_id', $this->id)
            ->orderBy('semillero.nombre')
            ->orderBy('proyecto.titulo')
            ->orderBy('usuario.nombre');
    }
} 