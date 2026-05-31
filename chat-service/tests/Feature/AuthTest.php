<?php

use Illuminate\Support\Facades\Http;

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
