<?php

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Projects\Models\HotbedModel;
use App\Modules\Projects\Models\UserModel;
use App\Modules\Projects\Models\UniversityModel;

class ProjectModel extends Model
{
    use HasFactory;

    // Desactivar timestamps por defecto
    public $timestamps = false;

    // Definir las columnas de timestamps personalizadas
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $table = 'proyecto';

    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'semillero_id',
        'lider_id',
        'coordinador_id'
    ];

    public function hotbet()
    {
        return $this->belongsTo(HotbedModel::class, 'semillero_id');
    }

    public function leader()
    {
        return $this->belongsTo(UserModel::class, 'lider_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(UserModel::class, 'coordinador_id');
    }

    public function students()
    {
        return $this->belongsToMany(UserModel::class, 'proyecto_usuario', 'proyecto_id', 'usuario_id');
    }

    public function university()
    {
        return $this->belongsTo(UniversityModel::class, 'universidad_id');
    }
}
