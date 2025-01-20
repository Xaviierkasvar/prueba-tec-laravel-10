<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Crear un usuario administrador
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Crear algunos usuarios de prueba
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Crear 5 usuarios aleatorios usando el factory
        User::factory()->count(5)->create();
    }
}