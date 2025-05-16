<?php

namespace App\Modules\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RolModel extends Model
{
    protected $table = "Rol";
    protected $primaryKey = "id";

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public $timestamps = false;


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Users\Models\UserModel::class,
            'Usuario_Rol',
            'rol_id',
            'usuario_id'
        );
    }

}