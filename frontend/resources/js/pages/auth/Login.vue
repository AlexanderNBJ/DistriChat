<script setup lang="ts">
import axios from 'axios';
import { useForm, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { ref } from 'vue';
import { MessageSquare, ArrowRight } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const processing = ref(false);

const submit = async () => {
    processing.value = true;
    try {
        const response = await axios.post(`${import.meta.env.VITE_AUTH_SERVICE_URL}/login`, {
            email: form.email,
            password: form.password,
        });

        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            window.location.href = '/dashboard';
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Credenciais inválidas');
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Log in" />

    <div class="flex flex-col gap-8 w-full max-w-sm mx-auto">
        <!-- BRANDING / HEADER -->
        <div class="flex flex-col items-center gap-3 text-center">
            <div class="flex aspect-square size-12 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                <MessageSquare class="size-7" />
            </div>
            <div class="space-y-1">
                <h1 class="text-2xl font-black tracking-tighter italic uppercase text-foreground">DistriChat</h1>
                <p class="text-sm text-muted-foreground font-medium">Bem-vindo de volta! Sentimos sua falta.</p>
            </div>
        </div>

        <div v-if="status" class="p-3 rounded-lg bg-emerald-500/10 text-emerald-600 text-center text-sm font-medium border border-emerald-500/20">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-4">
                <!-- E-MAIL -->
                <div class="grid gap-2">
                    <Label for="email" class="text-xs font-bold uppercase tracking-widest opacity-70">Endereço de E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        v-model="form.email"
                        placeholder="nome@exemplo.com"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- SENHA -->
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-xs font-bold uppercase tracking-widest opacity-70">Senha</Label>
                    </div>
                    <PasswordInput
                        id="password"
                        required
                        v-model="form.password"
                        placeholder="••••••••"
                        class="h-11 bg-muted/30 border-border/50 focus-visible:ring-indigo-500 rounded-xl px-4"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- LEMBRAR-ME -->
                <div class="flex items-center justify-between mt-1">
                    <Label for="remember" class="flex items-center gap-2 cursor-pointer group">
                        <Checkbox id="remember" v-model:checked="form.remember" class="border-border/50 data-[state=checked]:bg-indigo-600" />
                        <span class="text-sm font-medium text-muted-foreground group-hover:text-foreground transition-colors">Lembrar de mim</span>
                    </Label>
                </div>

                <!-- BOTÃO SUBMIT -->
                <Button
                    type="submit"
                    class="mt-2 h-12 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2 border-white/30 border-t-white" />
                    <span>Acessar o Chat</span>
                    <ArrowRight v-if="!processing" class="ml-2 size-4" />
                </Button>
            </div>

            <!-- FOOTER -->
            <div class="text-center text-sm font-medium text-muted-foreground">
                Novo por aqui?
                <TextLink :href="register()" class="text-indigo-600 hover:text-indigo-500 font-bold ml-1 underline underline-offset-4">
                    Criar uma conta gratuita
                </TextLink>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Adiciona uma animação suave de entrada */
form {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
