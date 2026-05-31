<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Garante a existência dos dois utilizadores com IDs estáticos para o k6
        User::updateOrCreate(['id' => 1], [
            'name' => 'Remetente k6',
            'email' => 'sender@k6.com',
            'password' => bcrypt('secret'),
        ]);

        User::updateOrCreate(['id' => 2], [
            'name' => 'Destinatario k6',
            'email' => 'receiver@k6.com',
            'password' => bcrypt('secret'),
        ]);

        // Injeta o token exato esperado pelo k6 (em texto limpo e em hash SHA-256)
        DB::table('personal_access_tokens')->updateOrInsert(
            ['tokenable_id' => 1, 'name' => 'k6-token'],
            [
                'tokenable_type' => 'App\Models\User',
                'token' => 'token-valido-de-teste',
                'abilities' => '["*"]',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

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
