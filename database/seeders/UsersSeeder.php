<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'João da Silva',
                'email' => 'joao.silva@example.com',
                'password' => Hash::make('senha123'),
            ],
            [
                'name' => 'Maria Oliveira',
                'email' => 'maria.oliveira@example.com',
                'password' => Hash::make('senha123'),
            ],
            [
                'name' => 'Carlos Santos',
                'email' => 'carlos.santos@example.com',
                'password' => Hash::make('senha123'),
            ],
            [
                'name' => 'Ana Pereira',
                'email' => 'ana.pereira@example.com',
                'password' => Hash::make('senha123'),
            ],
            [
                'name' => 'Lucas Almeida',
                'email' => 'lucas.almeida@example.com',
                'password' => Hash::make('senha123'),
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}
