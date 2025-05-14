<?php

namespace App\Modules\GestionDeSemilleros\Models;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'Evaluacion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
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
        'estado'
    ];

    // Campos calculados (generados automáticamente en la BD)
    protected $appends = [
        'puntuacion',
        'puntaje_total'
    ];

    // Casts para tipos de datos
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'dominio_tema' => 'integer',
        'manejo_auditorio' => 'integer',
    ];

    // Relación con Proyecto
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    // Relación con Evaluador (Usuario)
    public function evaluador()
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    // Accesor para puntuación (si no está como generated column)
    public function getPuntuacionAttribute()
    {
        return ($this->dominio_tema + $this->manejo_auditorio + $this->planteamiento_problema + 
                $this->justificacion + $this->objetivo_general + $this->objetivo_especifico + 
                $this->marco_teorico + $this->metodologia + $this->resultado_esperado + 
                $this->referencia_bibliografica) / 10.0;
    }

    // Accesor para puntaje total (si no está como generated column)
    public function getPuntajeTotalAttribute()
    {
        return $this->dominio_tema + $this->manejo_auditorio + $this->planteamiento_problema + 
               $this->justificacion + $this->objetivo_general + $this->objetivo_especifico + 
               $this->marco_teorico + $this->metodologia + $this->resultado_esperado + 
               $this->referencia_bibliografica;
    }

    // Scope para evaluaciones completadas
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    // Validación de campos de evaluación
    public static function validarCampos($request)
    {
        $campos = [
            'dominio_tema', 'manejo_auditorio', 'planteamiento_problema',
            'justificacion', 'objetivo_general', 'objetivo_especifico',
            'marco_teorico', 'metodologia', 'resultado_esperado',
            'referencia_bibliografica'
        ];

        foreach ($campos as $campo) {
            if ($request->has($campo) && ($request->$campo < 1 || $request->$campo > 5)) {
                return false;
            }
        }
        return true;
    }
}