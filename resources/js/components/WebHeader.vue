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
        <nav class="max-w-[1000px] mx-auto flex items-center justify-between p-4 lg:px-8" aria-label="Global">
            <!-- Logo -->
            <div class="flex lg:flex-1">
                <Link href="/" class="flex items-center gap-3 -m-1.5 p-1.5">
                    <div class="text-2xl">👨‍🍳</div>
                    <span class="text-xl font-bold text-[#ff6e02]">{{ $page.props.name }}</span>
                </Link>
            </div>

            <!-- Mobile menu button -->
            <div class="flex lg:hidden">
                <button
                    type="button"
                    class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-white hover:text-[#ff6e02]"
                    @click="mobileMenuOpen = true"
                >
                    <span class="sr-only">Open main menu</span>
                    <Menu class="h-6 w-6" aria-hidden="true" />
                </button>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex lg:gap-x-6">
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
            <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center lg:gap-3">
                <!-- Create Post Button -->
                <Link
                    v-if="user"
                    href="/posts/create"
                    class="bg-[#ff6e02] text-white px-4 py-2 rounded text-sm hover:bg-[#e55a00] transition-colors flex items-center gap-2"
                >
                    <PlusCircle class="w-4 h-4" />
                    发菜谱
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
                        <DropdownMenuContent align="end" class="w-56">
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
        </nav>

        <!-- Mobile Menu -->
        <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
            <div class="fixed inset-0 z-50" />
            <DialogPanel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-[#1c1c1c] px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-[#374151]">
                <!-- Mobile menu header -->
                <div class="flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-3 -m-1.5 p-1.5">
                        <div class="text-2xl">👨‍🍳</div>
                        <span class="text-xl font-bold text-[#ff6e02]">{{ $page.props.name }}</span>
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
                                    发菜谱
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

                                <!-- Logout -->
                                <Link
                                    href="/logout"
                                    method="post"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold text-white hover:bg-[#374151]"
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