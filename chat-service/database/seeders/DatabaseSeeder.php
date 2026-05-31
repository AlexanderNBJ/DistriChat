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
        // Criar apenas a base de utilizadores local sem disparar factories duplicadas
        DB::table('users')->updateOrInsert(['id' => 1], [
            'name' => 'Remetente k6',
            'email' => 'sender@k6.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->updateOrInsert(['id' => 2], [
            'name' => 'Destinatario k6',
            'email' => 'receiver@k6.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
