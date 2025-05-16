<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SemilleroUsuario extends Pivot
{
    protected $table = 'Semillero_Usuario';
    public $timestamps = true;

    protected $fillable = [
        'semillero_id',
        'usuario_id',
        'fecha_inscripcion'
    ];
}
