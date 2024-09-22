<?php
namespace App\Services;

use App\Services\DataStorageInterface;
use App\Models\User;

class PostgreSQLService implements DataStorageInterface
{
    public function createUser(array $userData): string
    {
        $user = User::create($userData);
        return $user->id;
    }

    public function updateUser(string $id, array $userData): void
    {
        $user = User::find($id);
        if ($user) {
            $user->update($userData);
        }
    }

    public function getUser(string $id): ?array
    {
        $user = User::find($id);
        return $user ? $user->toArray() : null;
    }

    public function getAllUsers(): array
    {
        return User::all()->toArray();
    }

    public function deleteUser(string $id): void
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
    }
}
