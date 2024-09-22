<?php

namespace App\Services;

use App\Services\DataStorageInterface;
use App\Repository\FirebaseUserRepository;
use Illuminate\Support\Facades\Log;
class FirebaseService implements DataStorageInterface
{
    protected $userRepository;
    protected $database;

    public function __construct(FirebaseUserRepository $userRepository)
    {
       
        $this->userRepository = $userRepository;
    }

    public function createUser(array $userData): string
    {
        $user = $this->userRepository->create($userData);
        return $user->id;
    }

    public function updateUser(string $id, array $userData): void
    {
        $this->userRepository->update($id, $userData);
    }

    public function getUser(string $id): ?array
    {
        $user = $this->userRepository->find($id);
        return $user ? $user->toArray() : null;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->all()->toArray();
    }

    public function deleteUser(string $id): void
    {
        $this->userRepository->delete($id);
    }
  
    public function getDatabase()
    {
        if ($this->database) {
            Log::info('Firebase Database initialized successfully.');
        } else {
            Log::error('Failed to initialize Firebase Database.');
        }
        
    }
    

}