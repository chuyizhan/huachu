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
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, MessageSquare, Crown, Trophy, PlusCircle, Shield, FolderTree, FileText, Coins, DollarSign } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.is_admin);

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

const adminNavItems: NavItem[] = [
    {
        title: '用户管理',
        href: '/admin/users',
        icon: Shield,
    },
    {
        title: '分类管理',
        href: '/admin/categories',
        icon: FolderTree,
    },
    {
        title: '帖子管理',
        href: '/admin/posts',
        icon: FileText,
    },
    {
        title: '积分交易',
        href: '/admin/point-transactions',
        icon: Coins,
    },
    {
        title: '金币交易',
        href: '/admin/credit-transactions',
        icon: DollarSign,
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
            <NavMain v-if="isAdmin" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
