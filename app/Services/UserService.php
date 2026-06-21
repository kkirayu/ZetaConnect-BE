<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function updateProfile(int $id, array $data): User
    {
        $user = $this->getById($id);
        $user->update($data);

        return $user;
    }
}
