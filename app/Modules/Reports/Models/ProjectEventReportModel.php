<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\ProjectModel;
use App\Modules\Events\Models\EventModel;

class ProjectEventReportModel extends Model
{
    protected $table = 'proyecto_evento';

    public $timestamps = false;

    protected $fillable = [
        'proyecto_id',
        'evento_id',
        'fecha_inscripcion',
        'observaciones',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'proyecto_id');
    }

    public function event()
    {
        return $this->belongsTo(EventModel::class, 'evento_id');
    }
} 