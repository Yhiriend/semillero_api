<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Faculties\Models\FacultyModel;

class UniversityReportModel extends Model
{
    protected $table = 'universidad';

    public function faculties()
    {
        return $this->hasMany(FacultyModel::class);
    }
} 