<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const CHAT_API = import.meta.env.VITE_CHAT_SERVICE_URL;
const messages = ref([]);
const newMessage = ref('');
const currentUser = JSON.parse(localStorage.getItem('user') || '{}');

// 1. Buscar histórico de mensagens
const fetchMessages = async () => {
    try {
        // Buscamos mensagens da "Sala 1" (exemplo)
        const response = await axios.get(`${CHAT_API}/messages?room_id=1`);
        messages.value = response.data;
    } catch (error) {
        console.error("Erro ao carregar mensagens", error);
    }
};

// 2. Enviar nova mensagem
const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    try {
        await axios.post(`${CHAT_API}/messages`, {
            content: newMessage.value,
            room_id: 1
        });
        newMessage.value = ''; // Limpa o campo
    } catch (error) {
        alert("Erro ao enviar. O serviço de chat está online?");
    }
};

onMounted(() => {
    // Verificar se está logado (Redireciona se não tiver token)
    if (!localStorage.getItem('token')) {
        window.location.href = '/login';
        return;
    }

    fetchMessages();

    // 3. OUVIR O WEBSOCKET (ALTA DISPONIBILIDADE)
    window.Echo.channel('chat.room.1')
        .listen('.message.sent', (e: any) => {
            console.log("Mensagem recebida via WebSocket:", e);
            messages.value.push(e.message);
        });
});
</script>

<template>
    <Head title="Chat Distribuído" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4">Sala Pública #1</h3>

                    <!-- Área de Mensagens -->
                    <div class="h-96 overflow-y-auto border p-4 mb-4 bg-gray-50 flex flex-col gap-2">
                        <div v-for="msg in messages" :key="msg.id"
                             :class="['p-2 rounded-lg max-w-[80%]', msg.sender_id === currentUser.id ? 'bg-blue-100 self-end' : 'bg-gray-200 self-start']">
                            <p class="text-xs font-bold text-gray-600">Usuário #{{ msg.sender_id }}</p>
                            <p>{{ msg.content }}</p>
                        </div>
                    </div>

                    <!-- Input de Envio -->
                    <div class="flex gap-2">
                        <input v-model="newMessage" @keyup.enter="sendMessage"
                               class="flex-1 border rounded p-2" placeholder="Digite sua mensagem...">
                        <button @click="sendMessage" class="bg-blue-600 text-white px-4 py-2 rounded">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
