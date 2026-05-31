<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Garante que o banco de dados de teste seja limpo a cada execução
uses(RefreshDatabase::class);

test('deve registrar um novo usuario com sucesso', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Alexander Barbosa',
        'email' => 'alex@cefet.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['message', 'user', 'token']);

    $this->assertDatabaseHas('users', ['email' => 'alex@cefet.com']);
});

test('deve fazer login com credenciais validas', function () {
    // Cria um usuário mockado no banco de testes
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['token']);
});

test('nao deve permitir login com senha errada', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'senha_errada',
    ]);

    $response->assertStatus(401)
             ->assertJson([
                 'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.'
             ]);
});

test('deve acessar os dados do usuario usando o token obtido', function () {
    $user = User::factory()->create();

    // ActAs simula a autenticação via Sanctum no teste
    $response = $this->actingAs($user)
                     ->getJson('/api/user');

    $response->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
});
