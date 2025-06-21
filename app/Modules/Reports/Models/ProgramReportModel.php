<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramReportModel extends Model
{
    protected $table = 'Programa';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'facultad_id',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'id' => 'integer',
        'nombre' => 'string',
        'descripcion' => 'string',
        'facultad_id' => 'integer',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Faculties\Models\FacultyModel::class, 'facultad_id', 'id');
    }

        public function seedbeds()
    {
        return $this->hasMany(SeedbedModel::class, 'programa_id', 'id');
    }
}