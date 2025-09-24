<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
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
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, MessageSquare, Crown, Trophy, PlusCircle } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: '首页',
        href: '/',
        icon: LayoutGrid,
    },
    {
        title: '社区',
        href: '/community',
        icon: Users,
    },
    {
        title: '菜谱',
        href: '/community/posts',
        icon: MessageSquare,
    },
    {
        title: '大厨',
        href: '/community/creators',
        icon: Users,
    },
    {
        title: '排行榜',
        href: '/community/leaderboard',
        icon: Trophy,
    },
];

const creatorNavItems: NavItem[] = [
    {
        title: '发布菜谱',
        href: '/posts/create',
        icon: PlusCircle,
    },
    {
        title: '我的菜谱',
        href: '/posts',
        icon: MessageSquare,
    },
    {
        title: 'VIP会员',
        href: '/vip',
        icon: Crown,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
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

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="creatorNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
