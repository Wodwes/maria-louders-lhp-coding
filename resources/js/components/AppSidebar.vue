<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BarChart3, LayoutGrid, ListOrdered, Map } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Event Overview',
        href: '/events',
        icon: ListOrdered,
    },
    {
        title: 'Poster Grid',
        href: '/events-visual-1',
        icon: BarChart3,
    },
    {
        title: 'Schedule Timeline',
        href: '/events-visual-2',
        icon: Map,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-emerald-950/20 bg-sidebar text-sidebar-foreground"
    >
        <SidebarHeader class="px-3 py-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="overflow-hidden px-3">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-white/10 p-3">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
