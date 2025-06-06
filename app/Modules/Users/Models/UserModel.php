<?php

namespace App\Modules\Users\Models;

use App\Modules\Evaluations\Models\EvaluationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserModel extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Usuario';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'contraseña',
        'tipo',
        'programa_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'contraseña',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
            'contraseña' => 'hashed',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    /**
     * Get the JWT identifier for the user.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the JWT custom claims.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'tipo' => $this->tipo,
            'programa_id' => $this->programa_id
        ];
    }

    /**
     * Relación con el programa
     */
    public function programa()
    {
        return $this->belongsTo(\App\Modules\Programs\Models\ProgramModel::class, 'programa_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            \App\Modules\Roles\Models\RolModel::class,
            'Usuario_Rol',
            'usuario_id',
            'rol_id'
        );
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    // Método para verificar roles exactos
    public function hasExactRole($role): bool
    {
        return $this->roles()->where('nombre', $role)->exists();
    }

    public function evaluaciones()
    {
        return $this->hasMany(EvaluationModel::class, 'evaluador_id');
    }
}