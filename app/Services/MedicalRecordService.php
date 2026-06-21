<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MedicalRecord;
use Illuminate\Pagination\LengthAwarePaginator;

class MedicalRecordService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return MedicalRecord::with(['pet', 'doctor', 'diagnosis'])->latest()->paginate($perPage);
    }

    public function getById(int $id): MedicalRecord
    {
        return MedicalRecord::with(['pet', 'doctor', 'diagnosis', 'labResults'])->findOrFail($id);
    }

    public function create(array $data): MedicalRecord
    {
        return MedicalRecord::create($data);
    }

    public function update(int $id, array $data): MedicalRecord
    {
        $record = $this->getById($id);
        $record->update($data);

        return $record;
    }

    public function delete(int $id): bool
    {
        $record = $this->getById($id);

        return $record->delete();
    }
}
