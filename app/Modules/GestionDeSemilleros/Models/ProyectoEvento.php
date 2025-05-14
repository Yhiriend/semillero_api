<?php

namespace App\Modules\GestionDeSemilleros\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoEvento extends Model
{
    protected $table = 'Proyecto_Evento'; // Nombre exacto de tu tabla
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'proyecto_id',
        'evento_id'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}