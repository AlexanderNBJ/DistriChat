<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Send, Hash, User as UserIcon, MoreVertical, Search, Paperclip } from 'lucide-vue-next';

const CHAT_API = import.meta.env.VITE_CHAT_SERVICE_URL;
const AUTH_API = import.meta.env.VITE_AUTH_SERVICE_URL;

const messages = ref([]);
const users = ref([]);
const selectedUser = ref(null);
const newMessage = ref('');
const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
const scrollContainer = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    if (scrollContainer.value) {
        scrollContainer.value.scrollTo({ top: scrollContainer.value.scrollHeight, behavior: 'smooth' });
    }
};

const fetchUsers = async () => {
    const response = await axios.get(`${AUTH_API}/users`);
    users.value = response.data.filter((u: any) => u.id !== currentUser.id);
};

const getUserName = (id: number) => {
    if (id === currentUser.id) return 'Você';
    const user = users.value.find((u: any) => u.id === id);
    return user ? user.name : `Usuário #${id}`;
};

const getInitials = (name: string) => name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

const getUserColor = (id: number) => {
    const colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500', 'bg-violet-500', 'bg-cyan-500'];
    return colors[id % colors.length];
};

const fetchMessages = async () => {
    let url = selectedUser.value
        ? `${CHAT_API}/messages?receiver_id=${selectedUser.value.id}`
        : `${CHAT_API}/messages?room_id=1`;

    const response = await axios.get(url);
    messages.value = response.data;
    scrollToBottom();
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;
    const payload = selectedUser.value
        ? { content: newMessage.value, receiver_id: selectedUser.value.id }
        : { content: newMessage.value, room_id: 1 };

    const response = await axios.post(`${CHAT_API}/messages`, payload);
    messages.value.push(response.data.data);
    newMessage.value = '';
    scrollToBottom();
};

const selectUser = (user: any) => {
    selectedUser.value = user;
    fetchMessages();
};

onMounted(async () => {
    await fetchUsers();
    await fetchMessages();

    window.Echo.channel('chat.room.1').listen('.message.sent', (e: any) => {
        if (!selectedUser.value) {
            messages.value.push(e.message);
            scrollToBottom();
        }
    });

    window.Echo.channel(`chat.user.${currentUser.id}`).listen('.message.sent', (e: any) => {
        if (selectedUser.value && e.message.sender_id === selectedUser.value.id) {
            messages.value.push(e.message);
            scrollToBottom();
        }
    });
});
</script>

<template>
    <Head title="Mensagens" />

    <div class="absolute inset-0 flex overflow-hidden bg-[#f8fafc] dark:bg-[#0f172a]">

        <!-- SIDEBAR DE CONTATOS -->
        <aside class="w-80 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl">
            <div class="p-6">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Conversas</h2>
                <div class="mt-4 relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input type="text" placeholder="Buscar..." class="w-full pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 transition-all" />
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 space-y-1 custom-scrollbar">
                <button @click="selectUser(null)"
                    :class="['w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200 group',
                             !selectedUser ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400']">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-colors', !selectedUser ? 'bg-white/20' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600']">
                        <Hash class="w-5 h-5" />
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-sm">Sala Geral</p>
                        <p :class="['text-[11px]', !selectedUser ? 'text-indigo-100' : 'text-slate-400']">Broadcast Público</p>
                    </div>
                </button>

                <div class="px-4 pt-6 pb-2 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Mensagens Diretas</div>

                <button v-for="u in users" :key="u.id" @click="selectUser(u)"
                    :class="['w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200',
                             selectedUser?.id === u.id ? 'bg-white dark:bg-slate-800 shadow-md ring-1 ring-slate-200 dark:ring-slate-700' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400']">
                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-inner', getUserColor(u.id)]">
                        {{ getInitials(u.name) }}
                    </div>
                    <div class="text-left flex-1 min-w-0">
                        <p class="font-bold text-sm truncate text-slate-800 dark:text-slate-200">{{ u.name }}</p>
                        <p class="text-[11px] text-emerald-500 font-medium">Disponível</p>
                    </div>
                </button>
            </nav>
        </aside>

        <!-- ÁREA DO CHAT -->
        <main class="flex-1 flex flex-col min-w-0 bg-white dark:bg-[#0f172a]">

            <!-- HEADER -->
            <header class="h-16 border-b border-slate-200 dark:border-slate-800 flex items-center px-8 justify-between bg-white/80 dark:bg-slate-900/80 backdrop-blur-md z-20">
                <div class="flex items-center gap-4">
                    <div v-if="selectedUser" :class="['w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-bold text-white', getUserColor(selectedUser.id)]">
                        {{ getInitials(selectedUser.name) }}
                    </div>
                    <Hash v-else class="w-5 h-5 text-indigo-500" />
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ selectedUser ? selectedUser.name : 'sala-geral' }}</h2>
                    </div>
                </div>
                <Button variant="ghost" size="icon" class="rounded-full text-slate-400"><MoreVertical class="w-5 h-5" /></Button>
            </header>

            <!-- MENSAGENS -->
            <div ref="scrollContainer" class="flex-1 overflow-y-auto p-8 space-y-8 custom-scrollbar bg-slate-50/30 dark:bg-transparent">

                <div v-for="(msg, index) in messages" :key="msg.id"
                     :class="['flex flex-col max-w-[70%] group transition-all duration-300',
                              msg.sender_id === currentUser.id ? 'ml-auto items-end' : 'items-start']">

                    <!-- Nome do Remetente -->
                    <span v-if="!selectedUser && msg.sender_id !== currentUser.id"
                          class="text-[10px] font-bold text-slate-400 mb-2 px-1 tracking-wide">
                        {{ getUserName(msg.sender_id) }}
                    </span>

                    <div :class="['px-5 py-3 rounded-3xl text-[14px] shadow-sm leading-relaxed transition-transform group-hover:scale-[1.01]',
                                  msg.sender_id === currentUser.id
                                  ? 'bg-gradient-to-br from-indigo-600 to-violet-700 text-white rounded-tr-none shadow-indigo-200 dark:shadow-none'
                                  : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-tl-none']">
                        {{ msg.content }}
                    </div>

                    <span class="text-[9px] text-slate-400 mt-2 font-medium opacity-0 group-hover:opacity-100 transition-opacity px-2 uppercase">
                        Enviado às {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                    </span>
                </div>
            </div>

            <!-- INPUT AREA -->
            <div class="p-6 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800">
                <div class="max-w-5xl mx-auto flex items-center gap-4 bg-white dark:bg-slate-800 p-2 pl-5 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-700">
                    <Button variant="ghost" size="icon" class="text-slate-400 shrink-0"><Paperclip class="w-5 h-5" /></Button>
                    <input
                        v-model="newMessage"
                        @keyup.enter="sendMessage"
                        placeholder="Escreva aqui..."
                        class="flex-1 bg-transparent border-none focus:ring-0 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400"
                    />
                    <Button @click="sendMessage" size="icon" class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white w-10 h-10 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all hover:scale-105 active:scale-95">
                        <Send class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; }

/* Efeito de suavidade na entrada das mensagens */
.flex-col { animation: slideUp 0.3s ease-out; }
@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
