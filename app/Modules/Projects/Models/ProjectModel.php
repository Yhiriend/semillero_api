<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class ProjectModel extends Model
{
    protected $table = 'Proyecto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'titulo',
        'descripcion',
        'semillero_id',
        'coordinador_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Seedbeds\Models\SeedbedModel::class, 'semillero_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'coordinador_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'lider_id');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Events\Models\EventModel::class,
            'Proyecto_Evento',
            'proyecto_id',
            'evento_id'
        );
    }
}