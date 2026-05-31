<?php

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Garante que a base de dados de testes é limpa a cada execução
uses(TestCase::class, RefreshDatabase::class);


test('deve persistir uma mensagem privada (1:1) com sucesso', function () {
    $messageData = [
        'sender_id' => 1,
        'receiver_id' => 2,
        'content' => 'Olá! Tudo bem?',
    ];

    $message = Message::create($messageData);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'sender_id' => 1,
        'receiver_id' => 2,
        'room_id' => null,
        'content' => 'Olá! Tudo bem?',
    ]);
});

test('deve persistir uma mensagem de grupo (1:N) com sucesso', function () {
    $messageData = [
        'sender_id' => 1,
        'room_id' => 99, // ID de uma sala hipotética
        'content' => 'Malta, a reunião começou no canal de voz.',
    ];

    $message = Message::create($messageData);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'sender_id' => 1,
        'receiver_id' => null,
        'room_id' => 99,
        'content' => 'Malta, a reunião começou no canal de voz.',
    ]);
});
