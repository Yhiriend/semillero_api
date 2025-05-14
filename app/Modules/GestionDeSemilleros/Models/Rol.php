<?php
namespace App\Modules\GestionDeSemilleros\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'rol';

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_rol', 'rol_id', 'usuario_id');
    }
}
