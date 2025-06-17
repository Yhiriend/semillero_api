<?php

namespace App\Modules\Seedbeds\Repositories;

use Illuminate\Support\Facades\DB;

class InscriptionRepository
{
    protected string $table = 'semillero_usuario';

    public function insertAndReturn(int $seedbedId, int $userId)
    {
        DB::table($this->table)->insert([
            'semillero_id'      => $seedbedId,
            'usuario_id'        => $userId,
            'fecha_inscripcion' => now(),
        ]);

        return DB::table($this->table)
            ->where('semillero_id', $seedbedId)
            ->where('usuario_id', $userId)
            ->first();
    }

    public function exists(int $seedbedId, int $userId): bool
    {
        return DB::table($this->table)
            ->where('semillero_id', $seedbedId)
            ->where('usuario_id', $userId)
            ->exists();
    }

    public function insert(int $seedbedId, int $userId): bool
    {
        return DB::table($this->table)->insert([
            'semillero_id'      => $seedbedId,
            'usuario_id'        => $userId,
            'fecha_inscripcion' => now(),
        ]);
    }
}
