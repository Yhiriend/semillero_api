<?php

namespace App\Modules\Universities\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniversityModel extends Model
{
    protected $table = 'Universidad';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nombre',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'id' => 'integer',
        'nombre' => 'string',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function faculties(): HasMany
    {
        return $this->hasMany(\App\Modules\Faculties\Models\FacultyModel::class, 'universidad_id', 'id');
    }
}