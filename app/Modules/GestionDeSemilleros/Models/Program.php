<?php

namespace App\Modules\GestionDeSemilleros\Models;

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
        return $this->hasMany(Semillero::class, 'programa_id', 'id');
    }
}