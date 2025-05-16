<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class EventModel extends Model
{
    protected $table = 'Evento';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime', 
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'coordinador_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Activities\Models\ActivityModel::class,
            'evento_id',
            'id'
        );
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Projects\Models\ProjectModel::class,
            'Proyecto_Evento',
            'evento_id',
            'proyecto_id'
        );
    }

}