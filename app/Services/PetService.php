<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pet;

class PetService
{
    public function getById(int $id): Pet
    {
        return Pet::findOrFail($id);
    }

    public function updateProfile(int $id, array $data): Pet
    {
        $pet = $this->getById($id);
        $pet->update($data);

        return $pet;
    }
}
