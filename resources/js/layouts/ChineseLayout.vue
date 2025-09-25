<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Users, MessageSquare, ChefHat, Trophy, Crown, PlusCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    nav_route: string | null;
    is_nav_item: boolean;
}

const page = usePage();

// Access globally shared navigation categories
const navCategories = computed(() => {
    return (page.props.navCategories as Category[]) || [];
});
</script>

<template>
    <div class="min-h-screen bg-[#1c1c1c] text-white">
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

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-[#1c1c1c] border-t border-[#374151] py-8 px-4">
            <div class="max-w-[1000px] mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-white font-medium mb-3">关于我们</h3>
                        <ul class="space-y-2 text-sm text-[#999999]">
                            <li><Link href="/about" class="hover:text-[#ff6e02]">网站介绍</Link></li>
                            <li><Link href="/contact" class="hover:text-[#ff6e02]">联系我们</Link></li>
                            <li><Link href="/help" class="hover:text-[#ff6e02]">使用帮助</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-medium mb-3">用户服务</h3>
                        <ul class="space-y-2 text-sm text-[#999999]">
                            <li><Link href="/creators/apply" class="hover:text-[#ff6e02]">申请认证</Link></li>
                            <li><Link href="/vip" class="hover:text-[#ff6e02]">VIP会员</Link></li>
                            <li><Link href="/rules" class="hover:text-[#ff6e02]">社区规则</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-medium mb-3">热门分类</h3>
                        <ul class="space-y-2 text-sm text-[#999999]">
                            <li><Link href="/community/posts?category=sichuan" class="hover:text-[#ff6e02]">川菜</Link></li>
                            <li><Link href="/community/posts?category=cantonese" class="hover:text-[#ff6e02]">粤菜</Link></li>
                            <li><Link href="/community/posts?category=home-cooking" class="hover:text-[#ff6e02]">家常菜</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-medium mb-3">联系方式</h3>
                        <div class="space-y-2 text-sm text-[#999999]">
                            <p>客服QQ：123456789</p>
                            <p>邮箱：contact@huachu.com</p>
                            <p>工作时间：9:00-18:00</p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-[#374151] mt-6 pt-4 text-center text-sm text-[#999999]">
                    <p>&copy; 2024 华厨社区. 版权所有</p>
                </div>
            </div>
        </footer>
    </div>
</template>