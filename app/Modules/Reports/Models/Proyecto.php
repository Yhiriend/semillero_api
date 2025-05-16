<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Reports\Models\Eventos;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'Proyecto';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'titulo',
        'descripcion',
        'semillero_id',
        'lider_id',
        'coordinador_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function semillero()
    {
        return $this->belongsTo(Semillero::class, 'semillero_id');
    }
    /**
     * Get the users (authors) associated with the project.
     */
    public function autores()
    {
        return $this->belongsToMany(
            \App\Modules\Users\Models\UserModel::class,
            'proyecto_usuario',
            'proyecto_id',
            'usuario_id'
        )->withPivot('fecha_asignacion');
    }

    /**
     * Get the leader of the project.
     */
    public function lider()
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'lider_id');
    }

    /**
     * Get the coordinator of the project.
     */
    public function coordinador()
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'coordinador_id');
    }

    // En Proyecto.php
    public function evento()
    {
        return $this->belongsTo(Eventos::class);
    }

} 

