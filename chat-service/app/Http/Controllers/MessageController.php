<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\MessageSent;

class MessageController extends Controller
{
    /**
     * Listar o histórico de mensagens.
     */
    public function index(Request $request): JsonResponse
    {
        // Obtemos o ID do utilizador atual (injetado pelo RemoteAuthMiddleware)
        $currentUserId = $request->user_data['id'];

        // Filtros opcionais enviados pelo cliente/front-end
        $receiverId = $request->query('receiver_id');
        $roomId = $request->query('room_id');

        $query = Message::query();

        if ($roomId) {
            // Se for uma sala, listamos todas as mensagens daquela sala
            $query->where('room_id', $roomId);
        } elseif ($receiverId) {
            // Se for chat privado, vai buscar as mensagens trocadas entre os dois utilizadores
            $query->where(function ($q) use ($currentUserId, $receiverId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $receiverId);
            })->orWhere(function ($q) use ($currentUserId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $currentUserId);
            });
        } else {
            return response()->json(['message' => 'Especifique um receiver_id ou room_id.'], 400);
        }

        // Retorna as mensagens ordenadas por ordem cronológica antiga -> recente
        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    /**
     * Guardar e enviar uma nova mensagem.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'receiver_id' => 'required_without:room_id|integer|nullable',
            'room_id' => 'required_without:receiver_id|integer|nullable',
        ]);

        $senderId = $request->user_data['id'];

        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $validated['receiver_id'] ?? null,
            'room_id' => $validated['room_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // Dispara o evento para o Laravel Reverb
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Mensagem enviada com sucesso!',
            'data' => $message
        ], 201);
    }
}
