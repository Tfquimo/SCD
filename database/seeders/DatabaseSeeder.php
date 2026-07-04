<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Define roles using Spatie permission
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleManager = Role::create(['name' => 'manager']);
        $roleEmployee = Role::create(['name' => 'employee']);

        // Create the default super admin user
        $admin = User::create([
            'name' => 'Administrador de Sistema',
            'email' => 'admin@scd.pt',
            'password' => Hash::make('Admin123!@#'), // Default strong password
            'role' => 'admin',
            'active' => true,
        ]);

        $admin->assignRole($roleAdmin);

        $this->command->info('Administrador criado com sucesso! Email: admin@scd.pt | Senha: Admin123!@#');
    }
}
