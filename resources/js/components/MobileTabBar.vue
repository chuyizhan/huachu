<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Home, PlusCircle, Heart, User } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

interface TabItem {
    path: string;
    icon: any;
    label: string;
}

const tabs: TabItem[] = [
    {
        path: '/',
        icon: Home,
        label: '首页'
    },
    {
        path: '/posts/create',
        icon: PlusCircle,
        label: '发帖'
    },
    {
        path: '/favorites',
        icon: Heart,
        label: '收藏'
    },
    {
        path: '/profile',
        icon: User,
        label: '我的'
    }
];

// Check if current route matches the tab path
const isActive = (path: string) => {
    const currentUrl = page.url;
    if (path === '/') {
        return currentUrl === '/';
    }
    return currentUrl.startsWith(path);
};
</script>

<template>
    <!-- Mobile Tab Bar - Fixed to bottom -->
    <div class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-black border-t border-[#333333]">
        <div class="flex items-center justify-around h-16">
            <Link
                v-for="tab in tabs"
                :key="tab.path"
                :href="tab.path"
                class="flex-1 flex flex-col items-center justify-center gap-1 transition-colors"
                :class="isActive(tab.path) ? 'text-[#ff6e02]' : 'text-[#333333]'"
            >
                <component
                    :is="tab.icon"
                    class="w-6 h-6"
                    :class="isActive(tab.path) ? 'text-[#ff6e02]' : 'text-[#333333]'"
                />
                <span
                    class="text-xs font-medium"
                    :class="isActive(tab.path) ? 'text-[#ff6e02]' : 'text-[#333333]'"
                >
                    {{ tab.label }}
                </span>
            </Link>
        </div>
    </div>

    <!-- Spacer to prevent content from being hidden behind the fixed tab bar on mobile -->
    <div class="h-16 lg:hidden" />
</template>
