<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'proyecto';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'semillero_id',
        'lider_id',
        'coordinador_id',
        'estado',
        'fecha_inicio',
        'fecha_fin'
    ];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime'
    ];

    /**
     * Get the users (authors) associated with the project.
     */
    public function students()
    {
        return $this->belongsToMany(
            UserModel::class,
            'proyecto_usuario',
            'proyecto_id',
            'usuario_id'
        )->withPivot('fecha_asignacion');
    }

    /**
     * Get the leader of the project.
     */
    public function lider()
    {
        return $this->belongsTo(UserModel::class, 'lider_id');
    }

    /**
     * Get the coordinator of the project.
     */
    public function coordinador()
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }
    public function seedbed(): BelongsTo
    {
        return $this->belongsTo(HotbedModel::class, 'semillero_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'lider_id');
    }

    public function university()
    {
        return $this->belongsTo(UniversityModel::class, 'universidad_id');
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
