<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Users\Models\UserModel;
use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Activities\Models\ActivityModel;

class EventReportModel extends Model
{
    protected $table = 'Evento';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime', 
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityModel::class, 'evento_id', 'id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            ProjectModel::class,
            'Proyecto_Evento',
            'evento_id',
            'proyecto_id'
        );
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