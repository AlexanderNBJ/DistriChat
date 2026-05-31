<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * O objeto da mensagem que será transmitido.
     */
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Definir em quais canais o evento será transmitido.
     */
    public function broadcastOn(): array
    {
        // Se for uma mensagem de sala (1:N)
        if ($this->message->room_id) {
            return [
                new Channel('chat.room.' . $this->message->room_id)
            ];
        }

        // Se for uma mensagem privada (1:1), transmitimos num canal destinado ao destinatário
        return [
            new Channel('chat.user.' . $this->message->receiver_id)
        ];
    }

    /**
     * O nome com que o evento será recebido no Front-end.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
