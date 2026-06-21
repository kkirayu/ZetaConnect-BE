<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EPrescription;
use Illuminate\Pagination\Paginator;

class EPrescriptionService
{
    public function getAll(int $perPage = 10): Paginator
    {
        return EPrescription::with(['medicalRecord', 'product'])->latest()->paginate($perPage);
    }

    public function getById(int $id): EPrescription
    {
        return EPrescription::with(['medicalRecord', 'product'])->findOrFail($id);
    }

    public function create(array $data): EPrescription
    {
        return EPrescription::create($data);
    }

    public function update(int $id, array $data): EPrescription
    {
        $prescription = $this->getById($id);
        $prescription->update($data);

        return $prescription;
    }

    public function delete(int $id): bool
    {
        $prescription = $this->getById($id);

        return $prescription->delete();
    }
}
