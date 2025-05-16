<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultad';

    public function universidad()
    {
        return $this->belongsTo(Universidad::class);
    }

    public function programas()
    {
        return $this->hasMany(Programa::class);
    }
}
