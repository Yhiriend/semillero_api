<?php
namespace App\Modules\GestionDeSemilleros\Models;

use App\Modules\GestionDeSemilleros\Models\Semillero;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table    = 'proyecto';
    public $timestamps  = false;
    protected $fillable = [
        'titulo',
        'semillero_id',
        'descripcion',
        'semillero_id',
        'lider_id',
        'coordinador_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

// En app/Models/Proyecto.php
    public function semillero()
    {
        return $this->belongsTo(Semillero::class);
    }

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class);
    }

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'proyecto_usuario', 'proyecto_id', 'usuario_id');
    }

}
