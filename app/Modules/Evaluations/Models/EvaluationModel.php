<?php

namespace App\Modules\Evaluations\Models;

use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Users\Models\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationModel extends Model
{
    protected $table = 'evaluacion';

    protected $fillable = [
        'fecha_actualizacion',
        'fecha_creacion',
        'estado',
        'puntaje_total',
        'puntuacion',
        'referencia_bibliografica',
        'resultado_esperado',
        'metodologia',
        'marco_teorico',
        'objetivo_especifico',
        'objetivo_general',
        'justificacion',
        'planteamiento_problema',
        'manejo_auditorio',
        'dominio_tema',
        'comentarios',
        'evaluador_id',
        'proyecto_id'
    ];

    protected $casts = [
        'fecha_actualizacion' => 'datetime',
        'fecha_creacion' => 'datetime',
        'puntaje_total' => 'decimal:2',
        'puntuacion' => 'integer',
        'referencia_bibliografica' => 'integer',
        'resultado_esperado' => 'integer',
        'metodologia' => 'integer',
        'marco_teorico' => 'integer',
        'objetivo_especifico' => 'integer',
        'objetivo_general' => 'integer',
        'justificacion' => 'integer',
        'planteamiento_problema' => 'integer',
        'manejo_auditorio' => 'integer',
        'dominio_tema' => 'integer'
    ];

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'evaluador_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'proyecto_id');
    }
} 