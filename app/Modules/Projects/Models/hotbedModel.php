<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;

class HotbedModel extends Model
{
    protected $table = 'semillero';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function coordinator()
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }

    public function program()
    {
        return $this->belongsTo(ProgramModel::class, 'programa_id');
    }

    public function users()
    {
        return $this->belongsToMany(UserModel::class, 'semillero_usuario', 'semillero_id', 'usuario_id');
    }
}
