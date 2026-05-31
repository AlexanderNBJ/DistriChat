<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Events\MessageSent;
uses(RefreshDatabase::class);

test('deve permitir acesso se o token for valido no auth-service', function () {
    // Simulamos que o auth-service (porta 8000) respondeu 200 OK com os dados do usuário
    Http::fake([
        'http://127.0.0.1:8000/api/user' => Http::response([
            'id' => 1,
            'name' => 'Alexander',
            'email' => 'alex@cefet.com'
        ], 200)
    ]);

    // Fazemos a requisição para a rota do chat enviando um token qualquer
    $response = $this->withToken('token-valido-fake')
        ->getJson('/api/chat/test');

    // Asserções: o chat-service deve aceitar (200) e retornar os dados injetados pelo middleware
    $response->assertStatus(200)
        ->assertJsonPath('message', 'Conexão segura estabelecida!')
        ->assertJsonPath('user.email', 'alex@cefet.com');
});

test('deve barrar o acesso se o auth-service disser que o token é invalido', function () {
    // Simulamos que o auth-service respondeu 401 Unauthorized
    Http::fake([
        'http://127.0.0.1:8000/api/user' => Http::response([
            'message' => 'Unauthenticated.'
        ], 401)
    ]);

    // Fazemos a requisição com um token inválido
    $response = $this->withToken('token-invalido-fake')
        ->getJson('/api/chat/test');

    // Asserção: o middleware do chat deve retornar 401
    $response->assertStatus(401)
        ->assertJsonPath('message', 'Token inválido ou expirado no serviço de autenticação.');
});

test('deve retornar 503 se o auth-service estiver fora do ar', function () {
    // Simulamos que a requisição HTTP falhou (ex: connection refused)
    Http::fake([
        'http://127.0.0.1:8000/api/user' => Http::sequence()->pushStatus(500) // ou lançar uma exceção simulada
    ]);

    // Forçando o client HTTP a lançar uma exceção de conexão para cair no catch do seu middleware:
    Http::preventStrayRequests();

    $response = $this->withToken('qualquer-token')
        ->getJson('/api/chat/test');

    // Asserção: Deve retornar o status de serviço indisponível
    $response->assertStatus(503)
        ->assertJsonPath('message', 'Serviço de autenticação indisponível no momento.');
});

test('deve permitir enviar uma mensagem se estiver autenticado remotamente', function () {
    // Dizemos ao Laravel para intercetar os eventos (fake)
    Event::fake();

    Http::fake([
        'http://127.0.0.1:8000/api/user' => Http::response([
            'id' => 7,
            'name' => 'Alexander',
            'email' => 'alex@cefet.com'
        ], 200)
    ]);

    // Fazemos o envio
    $response = $this->withToken('token-valido')
        ->postJson('/api/messages', [
            'receiver_id' => 12,
            'content' => 'Testando a rota integrada com WebSocket!'
        ]);

    $response->assertStatus(201);

    // Asserção de Sistema Distribuído: Garante que o evento de tempo real foi despachado!
    Event::assertDispatched(MessageSent::class, function ($event) {
        return $event->message->sender_id === 7 && $event->message->receiver_id === 12;
    });
});

test('deve listar o histórico de mensagens privadas corretamente', function () {
    Http::fake([
        'http://127.0.0.1:8000/api/user' => Http::response(['id' => 7], 200)
    ]);

    // Criar mensagens prévias no banco de dados para o teste
    Message::create(['sender_id' => 7, 'receiver_id' => 12, 'content' => 'Primeira mensagem']);
    Message::create(['sender_id' => 12, 'receiver_id' => 7, 'content' => 'Resposta recebida']);
    Message::create(['sender_id' => 7, 'receiver_id' => 99, 'content' => 'Mensagem para outro utilizador']); // Não deve aparecer

    // Chamar o endpoint pedindo o histórico com o utilizador 12
    $response = $this->withToken('token-valido')
        ->getJson('/api/messages?receiver_id=12');

    $response->assertStatus(200)
        ->assertJsonCount(2) // Apenas as 2 mensagens relevantes devem voltar
        ->assertJsonPath('0.content', 'Primeira mensagem')
        ->assertJsonPath('1.content', 'Resposta recebida');
});
