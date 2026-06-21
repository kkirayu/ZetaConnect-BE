<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MedicalCertificate;
use Illuminate\Pagination\LengthAwarePaginator;

class MedicalCertificateService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return MedicalCertificate::with(['pet', 'doctor'])->latest()->paginate($perPage);
    }

    public function getById(int $id): MedicalCertificate
    {
        return MedicalCertificate::with(['pet', 'doctor'])->findOrFail($id);
    }

    public function create(array $data): MedicalCertificate
    {
        return MedicalCertificate::create($data);
    }

    public function update(int $id, array $data): MedicalCertificate
    {
        $certificate = $this->getById($id);
        $certificate->update($data);

        return $certificate;
    }

    public function delete(int $id): bool
    {
        $certificate = $this->getById($id);

        return $certificate->delete();
    }
}
