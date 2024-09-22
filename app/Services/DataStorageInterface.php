<?php

namespace App\Services;



interface DataStorageInterface
{
    public function createUser(array $userData): string;
    public function updateUser(string $id, array $userData): void;
    public function getUser(string $id): ?array;
    public function getAllUsers(): array;
    public function deleteUser(string $id): void;

    // public function createRole(array $roleData): string;
    // public function updateRole(string $id, array $roleData): void;
    // public function getRole(string $id): ?array;
    // public function getAllRoles(): array;
    // public function deleteRole(string $id): void;
}