<?php
namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\FacultyModel;
use App\Modules\Projects\Models\ProgramModel;
use App\Modules\Projects\Models\UserModel;
use App\Modules\Projects\Models\HotbedModel;
use App\Modules\Projects\Models\ProjectModel;

class UniversityModel extends Model
{
    protected $table = 'universidad';
    public $timestamps = false;
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'fecha_creacion',
        'fecha_actualizacion'
    ];

    public function faculties()
    {
        return $this->hasMany(FacultyModel::class, 'universidad_id');
    }

    public function programs()
    {
        return $this->hasMany(ProgramModel::class, 'universidad_id');
    }

    public function users()
    {
        return $this->hasMany(UserModel::class, 'universidad_id');
    }

    public function hotbeds()
    {
        return $this->hasMany(HotbedModel::class, 'universidad_id');
    }

    public function projects()
    {
        return $this->hasMany(ProjectModel::class, 'universidad_id');
    }
}