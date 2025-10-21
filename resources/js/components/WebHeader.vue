<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { PlusCircle, Menu, X, Coins, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Dialog, DialogPanel } from '@headlessui/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import AppLogo from '@/components/AppLogo.vue';

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
const mobileMenuOpen = ref(false);

// Access globally shared navigation categories
const navCategories = computed(() => {
    return (page.props.navCategories as Category[]) || [];
});

// Access auth data
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);
</script>

<template>
    <!-- Header -->
    <header class="bg-[#1c1c1c] border-b border-[#374151]">
        <nav class="max-w-[1000px] mx-auto" aria-label="Global">
            <!-- Mobile Layout - UniApp Style -->
            <div class="lg:hidden flex items-center justify-between u-p-20">
                <!-- Logo -->
                <Link href="/" class="flex items-center">
                    <img src="/logo.png" alt="Logo" class="w-12 h-auto" />
                </Link>

                <!-- Center Menu Items -->
                <div class="flex space-x-2 items-center">
                    <!-- Hard-coded Home Link -->
                    <Link
                        href="/"
                        class="text-white text-sm hover:text-[#ff6e02] transition-colors whitespace-nowrap"
                    >
                        首页
                    </Link>

                    <!-- Dynamic Categories (limit to 3 to keep 4 total items) -->
                    <Link
                        v-for="(category, index) in navCategories.slice(0, 3)"
                        :key="category.id"
                        :href="category.nav_route || '/'"
                        class="text-white text-sm hover:text-[#ff6e02] transition-colors whitespace-nowrap"
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <!-- Hamburger Menu Icon -->
                <button
                    type="button"
                    class="flex items-center justify-center text-white hover:text-[#ff6e02]"
                    @click="mobileMenuOpen = true"
                >
                    <span class="sr-only">Open menu</span>
                    <Menu class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Desktop Layout -->
            <div class="hidden lg:flex items-center justify-between p-4 lg:px-8">
                <!-- Logo -->
                <div class="flex lg:flex-1">
                    <Link href="/" class="flex items-center gap-3 -m-1.5 p-1.5">
                        <AppLogo />
                    </Link>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="flex gap-x-6">
                    <!-- Hard-coded Home Link -->
                    <Link
                        href="/"
                        class="text-sm font-semibold text-white hover:text-[#ff6e02] transition-colors"
                    >
                        首页
                    </Link>

                    <!-- Dynamic Categories -->
                    <Link
                        v-for="category in navCategories"
                        :key="category.id"
                        :href="category.nav_route || '/'"
                        class="text-sm font-semibold text-white hover:text-[#ff6e02] transition-colors"
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <!-- Desktop Actions -->
                <div class="flex lg:flex-1 lg:justify-end lg:items-center lg:gap-3">
                    <!-- Create Post Button -->
                    <Link
                        v-if="user"
                        href="/posts/create"
                        class="bg-[#ff6e02] text-white px-4 py-2 rounded text-sm hover:bg-[#e55a00] transition-colors flex items-center gap-2"
                    >
                        <PlusCircle class="w-4 h-4" />
                        发帖子
                    </Link>

                    <!-- User Menu for authenticated users -->
                    <div v-if="user" class="relative">
                        <DropdownMenu>
                            <DropdownMenuTrigger class="flex items-center">
                                <Avatar class="h-8 w-8">
                                    <AvatarImage
                                        v-if="user.avatar"
                                        :src="user.avatar"
                                        :alt="user.name"
                                    />
                                    <AvatarFallback class="bg-[#ff6e02] text-white font-semibold">
                                        {{ getInitials(user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 bg-[#1f2937] border-[#374151]">
                                <UserMenuContent :user="user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Login/Register for guests -->
                    <div v-else class="flex items-center gap-3">
                        <Link
                            href="/login"
                            class="text-sm font-semibold text-[#ff6e02] hover:text-white transition-colors"
                        >
                            登录
                        </Link>
                        <Link
                            href="/register"
                            class="text-sm font-semibold text-[#999999] hover:text-white transition-colors"
                        >
                            注册
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
            <div class="fixed inset-0 z-50" />
            <DialogPanel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-[#1c1c1c] px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-[#374151]">
                <!-- Mobile menu header -->
                <div class="flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-3 -m-1.5 p-1.5">
                        <AppLogo />
                    </Link>
                    <button
                        type="button"
                        class="-m-2.5 rounded-md p-2.5 text-white hover:text-[#ff6e02]"
                        @click="mobileMenuOpen = false"
                    >
                        <span class="sr-only">Close menu</span>
                        <X class="h-6 w-6" aria-hidden="true" />
                    </button>
                </div>

                <!-- Mobile menu content -->
                <div class="mt-6 flow-root">
                    <div class="-my-6 divide-y divide-[#374151]">
                        <!-- Navigation links -->
                        <div class="space-y-2 py-6">
                            <!-- Hard-coded Home Link -->
                            <Link
                                href="/"
                                class="-mx-3 block rounded-lg px-3 py-2 text-base font-semibold text-white hover:bg-[#374151] hover:text-[#ff6e02]"
                                @click="mobileMenuOpen = false"
                            >
                                首页
                            </Link>

                            <!-- Dynamic Categories -->
                            <Link
                                v-for="category in navCategories"
                                :key="category.id"
                                :href="category.nav_route || '/'"
                                class="-mx-3 block rounded-lg px-3 py-2 text-base font-semibold text-white hover:bg-[#374151] hover:text-[#ff6e02]"
                                @click="mobileMenuOpen = false"
                            >
                                {{ category.name }}
                            </Link>
                        </div>

                        <!-- Auth actions -->
                        <div class="py-6">
                            <div v-if="user" class="space-y-2">
                                <!-- Credits Balance -->
                                <div class="-mx-3 px-3 py-3 bg-[#374151] rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Coins class="h-5 w-5 text-[#ff6e02]" />
                                            <span class="text-sm text-[#999999]">金币余额:</span>
                                        </div>
                                        <span class="text-base font-bold text-[#ff6e02]">{{ parseFloat(user.credits || 0).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Recharge Button -->
                                <Link
                                    href="/recharge"
                                    class="bg-[#ff6e02] text-white px-4 py-3 rounded text-base font-semibold hover:bg-[#e55a00] transition-colors flex items-center gap-2 justify-center w-full"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Wallet class="w-4 h-4" />
                                    充值金币
                                </Link>

                                <!-- Create Post Button -->
                                <Link
                                    href="/posts/create"
                                    class="bg-[#374151] text-white px-4 py-3 rounded text-base font-semibold hover:bg-[#4B5563] transition-colors flex items-center gap-2 justify-center w-full border border-[#4B5563]"
                                    @click="mobileMenuOpen = false"
                                >
                                    <PlusCircle class="w-4 h-4" />
                                    发帖子
                                </Link>

                                <!-- Favorites -->
                                <Link
                                    href="/favorites"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-white hover:bg-[#374151]"
                                    @click="mobileMenuOpen = false"
                                >
                                    我的收藏
                                </Link>

                                <!-- User profile link -->
                                <Link
                                    href="/settings/profile"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-white hover:bg-[#374151]"
                                    @click="mobileMenuOpen = false"
                                >
                                    设置
                                </Link>

                                <!-- Footer-style Links from UniApp -->
                                <div class="-mx-3 px-3 py-3 border-t border-[#374151] mt-3">
                                    <p class="text-xs font-semibold text-[#999999] uppercase tracking-wider mb-2">更多</p>

                                    <!-- Privacy Policy -->
                                    <Link
                                        href="/privacy"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        隐私政策
                                    </Link>

                                    <!-- Terms of Service -->
                                    <Link
                                        href="/terms"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        服务条款
                                    </Link>

                                    <!-- Contact Us -->
                                    <Link
                                        href="/contact"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        联系我们
                                    </Link>

                                    <!-- Feedback -->
                                    <Link
                                        href="/feedback"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        意见反馈
                                    </Link>

                                    <!-- Credits Withdrawal -->
                                    <Link
                                        href="/credits/withdraw"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        金币提现
                                    </Link>

                                    <!-- Points Rules -->
                                    <Link
                                        href="/points/rules"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        积分规则
                                    </Link>

                                    <!-- About Us -->
                                    <Link
                                        href="/about"
                                        class="block rounded-lg px-3 py-2.5 text-sm text-[#999999] hover:bg-[#374151] hover:text-white"
                                        @click="mobileMenuOpen = false"
                                    >
                                        关于我们
                                    </Link>
                                </div>

                                <!-- Logout -->
                                <Link
                                    href="/logout"
                                    method="post"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-white hover:bg-[#374151] mt-3"
                                    @click="mobileMenuOpen = false"
                                >
                                    退出登录
                                </Link>
                            </div>

                            <div v-else class="space-y-2">
                                <Link
                                    href="/login"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-[#ff6e02] hover:bg-[#374151]"
                                    @click="mobileMenuOpen = false"
                                >
                                    登录
                                </Link>
                                <Link
                                    href="/register"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-[#999999] hover:bg-[#374151] hover:text-white"
                                    @click="mobileMenuOpen = false"
                                >
                                    注册
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </DialogPanel>
        </Dialog>
    </header>
</template>