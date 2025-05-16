<?php
namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\FacultyModel;
use App\Modules\Projects\Models\UserModel;
use App\Modules\Projects\Models\HotbedModel;
use App\Modules\Projects\Models\ProjectModel;

class ProgramModel extends Model
{
    protected $table = 'programa';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';


    protected $fillable = [
        'nombre',
        'descripcion',
        'facultad_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    // Relaciones
    public function faculty()
    {
        return $this->belongsTo(FacultyModel::class, 'facultad_id');
    }

    public function users()
    {
        return $this->hasMany(UserModel::class, 'programa_id');
    }

    public function hotbeds()
    {
        return $this->hasMany(HotbedModel::class, 'programa_id');
    }

    public function projects()
    {
        return $this->hasMany(ProjectModel::class, 'programa_id');
    }
}
