<?php

namespace App\Modules\Events\Models;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEventModel extends Model
{
    protected $table = 'Proyecto_Evento';
    protected $primaryKey = 'id';
    public $timestamps = false;
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
        return $this->belongsTo(App\Modules\Projects\Models\ProjectModel::class, 'proyecto_id');
    }
}