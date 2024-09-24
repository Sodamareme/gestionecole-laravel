<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nom' => 'Admin',
            'prenom' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'telephone' => '1234567890',
            'photo' => 'default.jpg',
            'statut' => 'active',
            'fonction' => 'Administrator',
        ]);

        User::create([
            'nom' => 'Coach',
            'prenom' => 'User',
            'email' => 'coach@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_COACH,
            'telephone' => '0987654321',
            'photo' => 'default.jpg',
            'statut' => 'active',
            'fonction' => 'Coach',
        ]);
    }
}
