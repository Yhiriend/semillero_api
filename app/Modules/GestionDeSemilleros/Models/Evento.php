<?php

namespace App\Modules\GestionDeSemilleros\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'Evento';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'coordinador_id',
        'estado'
    ];

    // Relación con el coordinador (Usuario)
    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    // Relación con proyectos (a través de la tabla pivote)
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'Proyecto_Evento', 'evento_id', 'proyecto_id');
    }
}