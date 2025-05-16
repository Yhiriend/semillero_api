<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Projects\Models\HotbedModel;
use App\Modules\Projects\Models\ProgramModel;
use App\Modules\Projects\Models\RoleModel;

class UserModel extends Model
{
    protected $table = 'usuario';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'email',
        'tipo',
        'contraseña',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function leaderProjects()
    {
        return $this->hasMany(ProjectModel::class, 'lider_id');
    }

    public function coordinatorProjects()
    {
        return $this->hasMany(ProjectModel::class, 'coordinador_id');
    }

    public function projects()
    {
        return $this->belongsToMany(ProjectModel::class, 'proyecto_usuario', 'usuario_id', 'proyecto_id')
            ->withTimestamps('fecha_asignacion');
    }

    public function hotbeds()
    {
        return $this->belongsToMany(HotbedModel::class, 'semillero_usuario', 'usuario_id', 'semillero_id');
    }

    public function program()
    {
        return $this->belongsTo(ProgramModel::class, 'programa_id');
    }

    public function roles()
    {
        return $this->belongsToMany(RoleModel::class, 'usuario_rol', 'usuario_id', 'rol_id');
    }
}
