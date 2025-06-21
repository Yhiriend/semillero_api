<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Users\Models\UserModel;
use App\Modules\Projects\Models\ProjectModel;

class SeedbedModel extends Model
{
    use HasFactory;

    protected $table = 'Semillero';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinador_id',
        'programa_id',
        'fecha_creacion',
        'fecha_actualizacion'
    ];
    

    public function users()
    {
        return $this->belongsToMany(UserModel::class, 'Semillero_Usuario', 'semillero_id', 'usuario_id')
                    ->withPivot('fecha_inscripcion');
    }

    public function projects()
    {
        return $this->hasMany(ProjectModel::class, 'semillero_id');
    }
} 