<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuários para o teste de carga k6
        User::updateOrCreate(['id' => 1], [
            'name' => 'Remetente k6',
            'email' => 'sender@k6.com',
            'password' => Hash::make('secret'),
        ]);

        User::updateOrCreate(['id' => 2], [
            'name' => 'Destinatario k6',
            'email' => 'receiver@k6.com',
            'password' => Hash::make('secret'),
        ]);

        User::updateOrCreate(['id' => 3], [
            'name' => 'Alexander',
            'email' => 'alex@teste.com',
            'password' => Hash::make('123456'),
        ]);

        DB::table('personal_access_tokens')->updateOrInsert(
            ['tokenable_id' => 1, 'name' => 'k6-token-hashed'],
            [
                'tokenable_type' => 'App\Models\User',
                'token' => hash('sha256', 'token-valido-de-teste'),
                'abilities' => '["*"]',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
