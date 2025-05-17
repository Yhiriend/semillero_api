<?php

namespace App\Modules\Reports\Models;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Reports\Models\Eventos;

class ProyectoEvento extends Model
{
    protected $table = 'proyecto_evento'; // Nombre exacto de la tabla

    public $timestamps = false; // No hay created_at ni updated_at estándar

    protected $fillable = [
        'proyecto_id',
        'evento_id',
        'fecha_inscripcion',
        'observaciones',
    ];

    // Relaciones
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function evento()
    {
        return $this->belongsTo(Eventos::class);
    }
}
