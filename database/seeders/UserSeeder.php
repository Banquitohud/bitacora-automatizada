<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Contraseña por defecto solo para desarrollo; definir GSI_DEFAULT_PASSWORD en .env en producción.
        $password = Hash::make((string) env('GSI_DEFAULT_PASSWORD', 'gsi2026'));

        $users = [
            ['name' => 'Sebastián', 'email' => env('GSI_ADMIN_EMAIL', 'admin@gsilab.local'), 'role' => 'admin'],
            ['name' => 'Adriana', 'email' => env('GSI_ADMIN_EMAIL2', 'admin2@gsilab.local'), 'role' => 'admin'],
             ['name' => 'Diego', 'email' => env('GSI_USER2_EMAIL', 'usuario2@gsilab.local'), 'role' => 'analyst'],
            ['name' => 'Alejandro', 'email' => env('GSI_USER3_EMAIL', 'usuario3@gsilab.local'), 'role' => 'analyst'],
            ['name' => 'Andres', 'email' => env('GSI_USER4_EMAIL', 'usuario4@gsilab.local'), 'role' => 'analyst'],

        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => $password, 'role' => $data['role'], 'is_active' => true]
            );
        }
    }
}
