<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DiagnosisDictionary;
use Illuminate\Pagination\LengthAwarePaginator;

class DiagnosisDictionaryService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return DiagnosisDictionary::latest()->paginate($perPage);
    }

    public function getById(int $id): DiagnosisDictionary
    {
        return DiagnosisDictionary::findOrFail($id);
    }

    public function create(array $data): DiagnosisDictionary
    {
        return DiagnosisDictionary::create($data);
    }

    public function update(int $id, array $data): DiagnosisDictionary
    {
        $diagnosis = $this->getById($id);
        $diagnosis->update($data);

        return $diagnosis;
    }

    public function delete(int $id): bool
    {
        $diagnosis = $this->getById($id);

        return $diagnosis->delete();
    }
}
