<script setup lang="ts">
import axios from 'axios';
import { useForm, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { ref } from 'vue';
import { MessageSquare, UserPlus, ArrowRight } from 'lucide-vue-next';

defineOptions({
    layout: {
        title: 'Criar conta',
        description: 'Junte-se à nossa rede de chat distribuído',
    },
});

const processing = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = async () => {
    processing.value = true;
    try {
        const response = await axios.post(`${import.meta.env.VITE_AUTH_SERVICE_URL}/register`, {
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            window.location.href = '/dashboard';
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Erro ao registrar usuário. Verifique os dados.');
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Criar Conta" />

    <div class="flex flex-col gap-8 w-full max-w-sm mx-auto">
        <!-- BRANDING / HEADER -->
        <div class="flex flex-col items-center gap-3 text-center">
            <div class="flex aspect-square size-12 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                <MessageSquare class="size-7" />
            </div>
            <div class="space-y-1">
                <h1 class="text-2xl font-black tracking-tighter italic uppercase text-foreground">DistriChat</h1>
                <p class="text-sm text-muted-foreground font-medium">Crie sua conta e comece a conversar.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <div class="grid gap-4">
                <!-- NOME -->
                <div class="grid gap-2">
                    <Label for="name" class="text-xs font-bold uppercase tracking-widest opacity-70">Nome Completo</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        autofocus
                        placeholder="Como quer ser chamado?"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- E-MAIL -->
                <div class="grid gap-2">
                    <Label for="email" class="text-xs font-bold uppercase tracking-widest opacity-70">E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        placeholder="seu@email.com"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- SENHA -->
                <div class="grid gap-2">
                    <Label for="password" class="text-xs font-bold uppercase tracking-widest opacity-70">Senha</Label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        required
                        placeholder="••••••••"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- CONFIRMAR SENHA -->
                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-xs font-bold uppercase tracking-widest opacity-70">Confirmar Senha</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        required
                        placeholder="Repita sua senha"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                </div>

                <!-- BOTÃO REGISTRAR -->
                <Button
                    type="submit"
                    class="mt-2 h-12 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2 border-white/30 border-t-white" />
                    <span>Criar conta gratuita</span>
                    <UserPlus v-if="!processing" class="ml-2 size-4" />
                </Button>
            </div>

            <!-- FOOTER -->
            <div class="text-center text-sm font-medium text-muted-foreground">
                Já tem uma conta?
                <TextLink :href="login()" class="text-indigo-600 hover:text-indigo-500 font-bold ml-1 underline underline-offset-4">
                    Fazer login
                </TextLink>
            </div>
        </form>
    </div>
</template>

<style scoped>
form {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
