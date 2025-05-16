<?php

namespace App\Modules\Seedbeds\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programa';
    protected $fillable = [
        'id',
        'nombre',
        'descripcion'
    ];
    public function semillero()
    {
        return $this->hasMany(Seedbed::class, 'programa_id', 'id');
    }
}