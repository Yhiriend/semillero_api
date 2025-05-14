<?php

namespace App\Modules\GestionDeSemilleros\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\GestionDeSemilleros\Models\User;

class Semillero extends Model
{
    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }
    public function programa()
    {
        return $this->belongsTo(Program::class, 'programa_id');
    }
        protected $table = 'semillero';
        protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];
    public $timestamps = false;
}
