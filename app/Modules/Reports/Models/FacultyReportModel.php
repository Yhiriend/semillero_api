<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Reports\Models\UniversityReportModel;

class FacultyReportModel extends Model
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
        return $this->belongsTo(UniversityReportModel::class, 'universidad_id', 'id');
    }

    public function programas(): HasMany
    {
        return $this->hasMany(\App\Modules\reports\Models\ProgramReportModel::class, 'facultad_id', 'id');
    }

}