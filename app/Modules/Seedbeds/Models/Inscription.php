<?php

namespace App\Modules\Seedbeds\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Models\UserModel;
use App\Modules\Programs\Models\ProgramModel;


class Inscription extends Model
{
    protected $table = 'semillero_usuario';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'semillero_id',
        'usuario_id',
        'fecha_inscripcion',
    ];

    public function student()
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }

    public function semillero()
    {
        return $this->belongsTo(Seedbed::class, 'semillero_id');
    }
}
