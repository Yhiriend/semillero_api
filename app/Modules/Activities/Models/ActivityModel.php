<?php

namespace App\Modules\Activities\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityModel extends Model
{
    protected $table = 'Actividad';
    protected $primaryKey = 'id';
    protected $fillable = [
        'titulo',
        'descripcion',
        'evento_id',
        'semillero_id',
        'proyecto_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    public function event(): BelongsTo
    {
        return $this->belongsTo(
            \App\Modules\Events\Models\EventModel::class,
            'evento_id',
            'id'
        );
    }

    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Users\Models\UserModel::class,
            'Actividad_Responsable',
            'actividad_id',
            'responsable_id'
        );
    }
}