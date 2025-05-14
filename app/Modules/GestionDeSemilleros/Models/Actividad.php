<?php

namespace App\Modules\GestionDeSemilleros\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\GestionDeSemilleros\Models\Proyecto;
class Actividad extends Model
{
    protected $table = 'actividad';
    public $timestamps = false; // si usas campos manuales para fechas

    protected $fillable = [
        'titulo',
        'descripcion',
        'semillero_id',
        'proyecto_id',
        'evento_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    // Si quieres timestamps automáticos, puede omitir la línea anterior
    // y dejar que Eloquent gestione created_at/updated_at

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
