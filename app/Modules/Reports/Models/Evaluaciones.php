<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Reports\Models\Proyecto;
use App\Modules\Reports\Models\Usuario;
use App\Modules\Reports\Models\Eventos;

class Evaluaciones extends Model
{
    protected $table = 'Evaluacion';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'proyecto_id',
        'evaluador_id',
        'comentarios',
        'dominio_tema',
        'manejo_auditorio',
        'planteamiento_problema',
        'justificacion',
        'objetivo_general',
        'objetivo_especifico',
        'marco_teorico',
        'metodologia',
        'resultado_esperado',
        'referencia_bibliografica',
        'puntuacion',
        'puntaje_total',
        'estado',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'id' => 'integer',
        'proyecto_id' => 'integer',
        'evaluador_id' => 'integer',
        'comentarios' => 'string',
        'dominio_tema' => 'float',
        'manejo_auditorio' => 'float',
        'planteamiento_problema' => 'float',
        'justificacion' => 'float',
        'objetivo_general' => 'float',
        'objetivo_especifico' => 'float',
        'marco_teorico' => 'float',
        'metodologia' => 'float',
        'resultado_esperado' => 'float',
        'referencia_bibliografica' => 'float',
        'puntuacion' => 'float',
        'puntaje_total' => 'float',
        'estado' => 'string',
    ];

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'evaluador_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Projects\Models\ProjectModel::class, 'proyecto_id', 'id');
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'evaluador_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function evento()
    {
         return $this->belongsTo(Eventos::class, 'evento_id');
    }

}