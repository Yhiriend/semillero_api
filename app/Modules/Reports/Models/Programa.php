<?php

namespace App\Modules\Reports\Models;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $table = 'programa';

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function semilleros()
    {
        return $this->hasMany(Semillero::class);
    }
}
