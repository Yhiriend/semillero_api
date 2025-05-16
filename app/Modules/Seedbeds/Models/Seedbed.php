<?php

namespace App\Modules\Seedbeds\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Seedbeds\Models\UserModel;

class Seedbed extends Model
{
    public function coordinador()
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }
    public function programa()
    {
        return $this->belongsTo(Program::class, 'programa_id');
    }
        protected $table = 'semillero';
        protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];
    public $timestamps = false;
}
