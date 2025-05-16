<?php

namespace App\Modules\Faculties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function programs(): HasMany
    {
        return $this->hasMany(\App\Modules\Programs\Models\ProgramModel::class, 'facultad_id', 'id');
    }
}