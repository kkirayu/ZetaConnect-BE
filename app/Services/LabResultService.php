<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LabResult;
use Illuminate\Pagination\LengthAwarePaginator;

class LabResultService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return LabResult::with(['pet', 'doctor'])->latest()->paginate($perPage);
    }

    public function getById(int $id): LabResult
    {
        return LabResult::with(['pet', 'doctor'])->findOrFail($id);
    }

    public function create(array $data): LabResult
    {
        return LabResult::create($data);
    }

    public function update(int $id, array $data): LabResult
    {
        $labResult = $this->getById($id);
        $labResult->update($data);

        return $labResult;
    }

    public function delete(int $id): bool
    {
        $labResult = $this->getById($id);

        return $labResult->delete();
    }
}
