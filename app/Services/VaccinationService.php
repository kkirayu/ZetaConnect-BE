<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Vaccination;
use Illuminate\Pagination\LengthAwarePaginator;

class VaccinationService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Vaccination::with(['pet', 'doctor'])->latest()->paginate($perPage);
    }

    public function getById(int $id): Vaccination
    {
        return Vaccination::with(['pet', 'doctor'])->findOrFail($id);
    }

    public function create(array $data): Vaccination
    {
        return Vaccination::create($data);
    }

    public function update(int $id, array $data): Vaccination
    {
        $vaccination = $this->getById($id);
        $vaccination->update($data);

        return $vaccination;
    }

    public function delete(int $id): bool
    {
        $vaccination = $this->getById($id);

        return $vaccination->delete();
    }
}
