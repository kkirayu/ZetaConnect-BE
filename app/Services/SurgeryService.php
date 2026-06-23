<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Surgery;
use Illuminate\Pagination\LengthAwarePaginator;

class SurgeryService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Surgery::with(['pet', 'doctor'])->latest()->paginate($perPage);
    }

    public function getById(int $id): Surgery
    {
        return Surgery::with(['pet', 'doctor'])->findOrFail($id);
    }

    public function create(array $data): Surgery
    {
        return Surgery::create($data);
    }

    public function update(int $id, array $data): Surgery
    {
        $surgery = $this->getById($id);
        $surgery->update($data);

        return $surgery;
    }

    public function delete(int $id): bool
    {
        $surgery = $this->getById($id);

        return $surgery->delete();
    }
}
