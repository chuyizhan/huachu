<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editPassword } from '@/routes/password';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: '个人资料',
        href: editProfile(),
    },
    {
        title: '密码',
        href: editPassword(),
    },
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">设置</h1>
                <p class="text-[#999999]">管理你的个人资料和账户设置</p>
            </div>

            <div class="flex flex-col lg:flex-row lg:space-x-8">
                <aside class="w-full lg:w-64 mb-6 lg:mb-0">
                    <nav class="flex flex-col space-y-2">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            variant="ghost"
                            :class="[
                                'w-full justify-start text-left',
                                urlIsActive(item.href, currentPath)
                                    ? 'bg-[#ff6e02] text-white hover:bg-[#e55a00]'
                                    : 'text-[#999999] hover:bg-[#374151] hover:text-white',
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="h-4 w-4 mr-2" />
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="my-6 lg:hidden bg-[#4B5563]" />

                <div class="flex-1 max-w-3xl">
                    <section class="space-y-6">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
