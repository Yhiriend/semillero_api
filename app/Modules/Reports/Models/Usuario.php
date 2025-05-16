<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'Usuario';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'email',
        'tipo',
        'contraseña',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    protected $hidden = ['contraseña'];

    public function semilleros()
    {
        return $this->belongsToMany(Semillero::class, 'Semillero_Usuario', 'usuario_id', 'semillero_id')
                    ->withTimestamps();
    }
}
