<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário 1 - Remetente padrão para testes
        User::updateOrCreate(
            ['email' => 'sender@k6.com'],
            [
                'id' => 1,
                'name' => 'Remetente k6',
                'password' => Hash::make('secret'),
            ]
        );

        // Usuário 2 - Destinatário padrão para testes
        User::updateOrCreate(
            ['email' => 'receiver@k6.com'],
            [
                'id' => 2,
                'name' => 'Destinatario k6',
                'password' => Hash::make('secret'),
            ]
        );

        // Usuário para você usar no navegador
        User::updateOrCreate(
            ['email' => 'alex@teste.com'],
            [
                'id' => 3,
                'name' => 'Alexander',
                'password' => Hash::make('123456'),
            ]
        );
    }
}
