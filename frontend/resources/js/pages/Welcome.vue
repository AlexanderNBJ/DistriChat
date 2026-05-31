<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { MessageSquare, Zap, Globe, ShieldCheck, Cpu } from 'lucide-vue-next';

const isLogged = ref(false);

onMounted(() => {
    isLogged.value = !!localStorage.getItem('token');
});
</script>

<template>
    <Head title="Bem-vindo ao DistriChat" />

    <div class="min-h-screen bg-background text-foreground selection:bg-indigo-500/30 overflow-x-hidden">
        <!-- FUNDO TECNOLÓGICO (Grid de pontos sutil) -->
        <div class="absolute inset-0 bg-[radial-gradient(hsl(var(--muted-foreground)/0.15)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,#000_70%,transparent_100%)] -z-10"></div>

        <!-- HEADER / NAV -->
        <header class="container mx-auto px-6 py-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                    <MessageSquare class="size-6" />
                </div>
                <span class="font-black text-xl tracking-tighter italic uppercase">DistriChat</span>
            </div>

            <nav class="flex items-center gap-2">
                <template v-if="isLogged">
                    <Link :href="dashboard()" class="text-sm font-bold bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20">
                        Ir para o Chat
                    </Link>
                </template>
                <template v-else>
                    <Link :href="login()" class="text-sm font-bold px-4 py-2.5 hover:text-indigo-500 transition-colors">Entrar</Link>
                    <Link :href="register()" class="text-sm font-bold border border-border px-5 py-2.5 rounded-xl hover:bg-muted transition-all">Criar Conta</Link>
                </template>
            </nav>
        </header>

        <!-- HERO SECTION -->
        <main class="container mx-auto px-6 pt-20 pb-24 text-center">

            <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-6 max-w-4xl mx-auto leading-[1.1]">
                Comunicação sem <span class="text-indigo-600 italic">fronteiras.</span>
            </h1>

            <p class="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto mb-12 leading-relaxed">
                Uma plataforma de chat resiliente baseada em <span class="text-foreground font-bold decoration-indigo-500/50">Microsserviços</span>.
                Alta disponibilidade, escalabilidade horizontal e entrega instantânea.
            </p>

            <!-- FEATURES GRID (Explica os Sistemas Distribuídos) -->
            <div id="features" class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-32 text-left">
                <div class="p-8 rounded-3xl bg-card/50 border border-border backdrop-blur-sm group hover:border-indigo-500/50 transition-colors">
                    <div class="size-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                        <Globe class="size-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-3">Escalável</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Arquitetura distribuída que permite adicionar novos nós dinamicamente para suportar milhões de conexões simultâneas via Redis.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-card/50 border border-border backdrop-blur-sm group hover:border-indigo-500/50 transition-colors">
                    <div class="size-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                        <Cpu class="size-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-3">Independente</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Serviços de Autenticação e Chat desacoplados. Falhas em um domínio não afetam a disponibilidade do outro.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-card/50 border border-border backdrop-blur-sm group hover:border-indigo-500/50 transition-colors">
                    <div class="size-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                        <ShieldCheck class="size-6" />
                    </div>
                    <h3 class="text-xl font-bold mb-3">Seguro</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Validação remota de tokens JWT em tempo real, garantindo que apenas usuários autenticados acessem o barramento de mensagens.
                    </p>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="container mx-auto px-6 py-12 border-t border-border/50 text-center">
            <p class="text-sm text-muted-foreground font-medium uppercase tracking-widest">
                Trabalho Final - Sistemas Distribuídos - 2026/1
            </p>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.8s ease-out forwards;
}
</style>
