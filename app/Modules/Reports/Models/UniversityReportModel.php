<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Reports\Models\FacultyReportModel;

class UniversityReportModel extends Model
{
    protected $table = 'universidad';

    public function faculties()
    {
        return $this->hasMany(FacultyReportModel::class, 'universidad_id', 'id');
    }
} 