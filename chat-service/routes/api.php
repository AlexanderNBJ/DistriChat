<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

// Rota protegida pela validação remota de microsserviços
Route::middleware('auth.remote')->group(function () {

    // Teste de integração rápida
    Route::get('/chat/test', function (Request $request) {
        return response()->json([
            'message' => 'Conexão segura estabelecida!',
            'user' => $request->user_data
        ]);
    });

    // Endpoints do Chat
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
});
