<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class EventModel extends Model
{
    protected $table = 'evento';
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
        'fecha_inicio' => 'datatime',
        'fecha_fin' => 'datatime',
        'fecha_creacion' => 'datatime',
        'fecha_' => 'datatime',
    ];

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\UserModel::class, 'coordinador_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityModel::class, 'evento_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ProjectEventModel::class, 'evento_id');
    }
}