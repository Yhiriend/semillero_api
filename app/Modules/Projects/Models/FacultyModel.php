<?php
namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\UniversityModel;
use App\Modules\Projects\Models\ProgramModel;
use App\Modules\Projects\Models\UserModel;
use App\Modules\Projects\Models\HotbedModel;
use App\Modules\Projects\Models\ProjectModel;

class FacultyModel extends Model
{
    protected $table = 'facultad';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'universidad_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function university()
    {
        return $this->belongsTo(UniversityModel::class, 'universidad_id');
    }

    public function programs()
    {
        return $this->hasMany(ProgramModel::class, 'facultad_id');
    }

    public function users()
    {
        return $this->hasMany(UserModel::class, 'facultad_id');
    }

    public function hotbeds()
    {
        return $this->hasMany(HotbedModel::class, 'facultad_id');
    }

    public function projects()
    {
        return $this->hasMany(ProjectModel::class, 'facultad_id');
    }
}