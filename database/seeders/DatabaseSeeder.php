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
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleManager = Role::firstOrCreate(['name' => 'manager']);
        $roleEmployee = Role::firstOrCreate(['name' => 'employee']);

        // Create the default super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@scd.pt'],
            [
                'name' => 'Administrador de Sistema',
                'password' => Hash::make('Admin123!@#'), // Default strong password
                'role' => 'admin',
                'active' => true,
            ]
        );

        $admin->assignRole($roleAdmin);

        $this->command->info('Administrador criado com sucesso! Email: admin@scd.pt | Senha: Admin123!@#');
    }
}
