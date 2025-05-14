<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    protected $table = 'rol';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function users()
    {
        return $this->belongsToMany(UserModel::class, 'usuario_rol', 'rol_id', 'usuario_id');
    }
}
