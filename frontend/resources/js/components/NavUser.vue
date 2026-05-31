<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ChevronsUpDown, LogOut, Settings, User } from 'lucide-vue-next';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

const userData = ref({ name: 'Usuário', email: '' });

onMounted(() => {
    const stored = localStorage.getItem('user');
    if (stored) userData.value = JSON.parse(stored);
});

const logout = () => {
    localStorage.clear();
    window.location.href = '/login';
};

const getInitials = (name: string) => name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg" class="hover:bg-muted transition-colors">
                        <Avatar class="h-8 w-8 rounded-lg border border-border">
                            <AvatarFallback class="rounded-lg bg-indigo-600 text-white font-bold text-[10px]">
                                {{ getInitials(userData.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                            <span class="truncate font-bold text-foreground">{{ userData.name }}</span>
                            <span class="truncate text-[10px] text-emerald-500 font-medium">Online</span>
                        </div>
                        <ChevronsUpDown class="ml-auto size-4 opacity-50" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56 rounded-xl" side="right" align="end" :side-offset="8">
                    <DropdownMenuLabel class="text-xs uppercase opacity-50 font-black tracking-widest">Minha Conta</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem class="cursor-pointer">
                            <User class="mr-2 size-4" /> Perfil
                        </DropdownMenuItem>
                        <DropdownMenuItem class="cursor-pointer">
                            <Settings class="mr-2 size-4" /> Configurações
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="logout" class="text-red-500 focus:text-red-500 cursor-pointer">
                        <LogOut class="mr-2 size-4" /> Sair do Chat
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
