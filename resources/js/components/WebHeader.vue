<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    nav_route: string | null;
    is_nav_item: boolean;
    icon?: string;
    color?: string;
}

const page = usePage();

// Access globally shared navigation categories
const navCategories = computed(() => {
    return (page.props.navCategories as Category[]) || [];
});
</script>

<template>
    <!-- Header -->
    <header class="bg-[#1c1c1c] border-b border-[#374151]">
        <div class="max-w-[1000px] mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3">
                    <div class="text-2xl">👨‍🍳</div>
                    <span class="text-xl font-bold text-[#ff6e02]">华厨社区</span>
                </Link>

                <!-- Navigation -->
                <nav class="hidden lg:flex items-center gap-6">
                    <Link
                        v-for="category in navCategories"
                        :key="category.id"
                        :href="category.nav_route || '/'"
                        class="text-sm text-white hover:text-[#ff6e02] transition-colors"
                    >
                        {{ category.name }}
                    </Link>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <Link
                        href="/posts/create"
                        class="bg-[#ff6e02] text-white px-4 py-2 rounded text-sm hover:bg-[#e55a00] transition-colors flex items-center gap-2"
                    >
                        <PlusCircle class="w-4 h-4" />
                        发菜谱
                    </Link>
                    <Link
                        href="/login"
                        class="text-[#ff6e02] hover:text-white text-sm transition-colors"
                    >
                        登录
                    </Link>
                    <Link
                        href="/register"
                        class="text-[#999999] hover:text-white text-sm transition-colors"
                    >
                        注册
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>