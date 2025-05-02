<?php

namespace App\Modules\Events\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEventModel extends Model
{
    protected $table = 'proyecto_evento';
    protected $primaryKey = 'id';
    protected $fillable = [
        'evento_id',
        'proyecto_id',
        'fecha_inscripcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventModel::class, 'evento_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Projects\Models\ProjectModel::class, 'proyecto_id');
    }
}