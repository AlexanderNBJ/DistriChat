<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // O ID do emissor vem do auth-service (guardamos apenas a referência numérica)
            $table->unsignedBigInteger('sender_id');

            // Para mensagens 1:1 (ID do utilizador destino)
            $table->unsignedBigInteger('receiver_id')->nullable();

            // Para mensagens 1:N (ID da sala/grupo destino)
            $table->unsignedBigInteger('room_id')->nullable();

            $table->text('content');
            $table->timestamps();

            // Índices para acelerar as consultas do histórico de chat
            $table->index(['sender_id', 'receiver_id']);
            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
