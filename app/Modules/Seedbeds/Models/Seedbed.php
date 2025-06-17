<?php

namespace App\Modules\Seedbeds\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Models\UserModel;
use App\Modules\Programs\Models\ProgramModel;

class Seedbed extends Model
{
    public function coordinador()
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }
    public function programa()
    {
        return $this->belongsTo(ProgramModel::class, 'programa_id');
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
    public function student()
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscription::class);
    }
    

}
