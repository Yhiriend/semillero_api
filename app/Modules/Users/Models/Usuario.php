<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuario';

    protected $fillable = [
        'nombre',
        'email',
        'tipo',
        'programa_id',
        'contraseña',
    ];

    protected $hidden = [
        'contraseña',
    ];

    public $timestamps = false;

    public static function obtenerEstudiantesYProfesores()
    {
        return self::whereIn('tipo', ['estudiante', 'profesor'])->get();
    }
}
