<script setup lang="ts">
import { MessageSquare, Settings2, ShieldCheck, Users } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';

const mainNavItems = [
    {
        title: 'Mensagens',
        href: dashboard(),
        icon: MessageSquare,
        isActive: true
    }
];

const footerNavItems = [
    { title: 'Segurança', href: '#', icon: ShieldCheck },
    { title: 'Configurações', href: '#', icon: Settings2 },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar" class="border-r border-border/50">
        <SidebarHeader class="h-16 border-b border-border/50 flex justify-center">
            <SidebarMenu>
                <SidebarMenuItem>
                    <div class="flex items-center gap-3 px-2">
                        <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-lg">
                            <MessageSquare class="size-5" />
                        </div>
                        <div class="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                            <span class="truncate font-black tracking-tighter italic text-foreground uppercase">DISTRI CHAT</span>
                            <span class="truncate text-[10px] opacity-50 font-bold">CEFET-MG</span>
                        </div>
                    </div>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-background">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="bg-background border-t border-border/50">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
