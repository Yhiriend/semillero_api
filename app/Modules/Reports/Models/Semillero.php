<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semillero extends Model
{
    use HasFactory;

    protected $table = 'Semillero';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'Semillero_Usuario', 'semillero_id', 'usuario_id')
                    ->withTimestamps();
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'semillero_id');
    }
}
