<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityModel extends Model
{
    protected $table = 'actividad';
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
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventModel::class, 'evento_id');
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