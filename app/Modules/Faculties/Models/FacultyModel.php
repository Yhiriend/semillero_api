<?php

namespace App\Modules\Faculties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyModel extends Model
{
    protected $table = 'Facultad';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nombre',
        'universidad_id',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'id' => 'integer',
        'nombre' => 'string',
        'universidad_id' => 'integer',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Universities\Models\UniversityModel::class, 'universidad_id', 'id');
    }
}