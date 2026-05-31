<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Events\MessageSent;

uses(RefreshDatabase::class);

test('deve permitir acesso se o token for valido no auth-service', function () {
    // Usamos '*/api/user' para interceptar qualquer HOST que o middleware use
    Http::fake([
        '*/api/user' => Http::response([
            'id' => 1,
            'name' => 'Alexander',
            'email' => 'alex@cefet.com'
        ], 200)
    ]);

    $response = $this->withToken('token-valido-fake')
        ->getJson('/api/chat/test');

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Conexão segura estabelecida!')
        ->assertJsonPath('user.email', 'alex@cefet.com');
});

test('deve barrar o acesso se o auth-service disser que o token é invalido', function () {
    Http::fake([
        '*/api/user' => Http::response([
            'message' => 'Unauthenticated.'
        ], 401)
    ]);

    $response = $this->withToken('token-invalido-fake')
        ->getJson('/api/chat/test');

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Token inválido ou expirado no serviço de autenticação.');
});

test('deve retornar 503 se o auth-service estiver instavel', function () {
    // Simulamos um erro de servidor (500) vindo do outro microsserviço
    Http::fake([
        '*/api/user' => Http::response([], 500)
    ]);

    $response = $this->withToken('qualquer-token')
        ->getJson('/api/chat/test');

    $response->assertStatus(503)
        ->assertJsonPath('message', 'Serviço de autenticação indisponível no momento.');
});

test('deve permitir enviar uma mensagem se estiver autenticado remotamente', function () {
    Event::fake();

    Http::fake([
        '*/api/user' => Http::response([
            'id' => 7,
            'name' => 'Alexander',
            'email' => 'alex@cefet.com'
        ], 200)
    ]);

    $response = $this->withToken('token-valido')
        ->postJson('/api/messages', [
            'receiver_id' => 12,
            'content' => 'Testando a rota integrada com WebSocket!'
        ]);

    $response->assertStatus(201);

    // Garante que o evento de broadcast foi disparado para o Reverb/Redis
    Event::assertDispatched(MessageSent::class);
});

test('deve listar o histórico de mensagens privadas corretamente', function () {
    Http::fake([
        '*/api/user' => Http::response(['id' => 7], 200)
    ]);

    Message::create(['sender_id' => 7, 'receiver_id' => 12, 'content' => 'Primeira mensagem']);
    Message::create(['sender_id' => 12, 'receiver_id' => 7, 'content' => 'Resposta recebida']);

    $response = $this->withToken('token-valido')
        ->getJson('/api/messages?receiver_id=12');

    $response->assertStatus(200)
        ->assertJsonCount(2);
});
