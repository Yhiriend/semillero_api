<?php

namespace App\Modules\Seedbeds\Repositories;

use App\Modules\Seedbeds\Models\Seedbed;

class SeedbedRepository
{
    public function getAll(?string $search = null)
    {
        $query = Seedbed::with(['coordinador', 'programa']);

        if (!is_null($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                    ->orWhere('id', $search);
            });
        }

        return $query->get();
    }

    public function findById(int $id): ?Seedbed
    {
        return Seedbed::with(['coordinador', 'programa'])->find($id);
    }

    public function create(array $data): Seedbed
    {
        return Seedbed::create($data);
    }

    public function update(Seedbed $seedbed, array $data): bool
    {
        return $seedbed->update($data);
    }

    public function delete(Seedbed $seedbed): bool
    {
        return $seedbed->delete();
    }

    public function findRaw(int $id): ?Seedbed
    {
        return Seedbed::find($id);
    }
}
