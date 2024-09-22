<?php

namespace App\Repository;

use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use App\Repository\ReferentielRepository;
class FirebaseUserRepository implements UserRepositoryInterface
{
    protected $database;
    protected $users;
    protected $referentielRepository;


    public function __construct(ReferentielRepository $referentielRepository)
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $firebase->createDatabase();
        $this->users = $this->database->getReference('users');
        $this->referentielRepository = $referentielRepository;
    }

    public function create(array $data): User
    {
        $newUserRef = $this->database->getReference('users')->push($data);
        $id = $newUserRef->getKey();
        $user = new User();
        $user->id = $id;
        $user->fill($data);
        return $user;
    }

    public function update($id, array $data)
    {
        $this->users->getChild($id)->update($data);
        return new User($data + ['id' => $id]);
    }

    public function find($id)
    {
        $user = $this->users->getChild($id)->getValue();
        return $user ? new User($user + ['id' => $id]) : null;
    }

    public function all()
    {
        $users = $this->users->getValue() ?? [];
        return collect($users)->map(function ($user, $id) {
            return new User($user + ['id' => $id]);
        });
    }

    public function delete($id)
    {
        $this->users->getChild($id)->remove();
    }
}