<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    protected $table = 'universidad';

    public function facultades()
    {
        return $this->hasMany(Facultad::class);
    }
}
