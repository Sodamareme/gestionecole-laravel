<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Creating roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Coach']);
        Role::create(['name' => 'CM']);
        Role::create(['name' => 'Apprenant']);
        Role::create(['name' => 'Manager']);

        // Add more roles as necessary
    }
}
