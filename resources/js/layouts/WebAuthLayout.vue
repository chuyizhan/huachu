<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const currentYear = new Date().getFullYear();
</script>

<template>
    <div class="min-h-screen bg-[#1c1c1c] flex flex-col items-center justify-center px-4 py-8">
        <div class="w-full max-w-[400px]">
            <!-- Logo and Brand -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center justify-center mb-4">
                    <AppLogo />
                </Link>
                <div v-if="title || description" class="space-y-2">
                    <h1 v-if="title" class="text-xl font-bold text-white">{{ title }}</h1>
                    <p v-if="description" class="text-sm text-[#999999]">{{ description }}</p>
                </div>
            </div>

            <!-- Form Content -->
            <div class="bg-[#374151] rounded-lg p-6 shadow-lg">
                <slot />
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-xs text-[#999999]">
                <p>&copy; {{ currentYear }} {{ $page.props.name }}. 版权所有</p>
            </div>
        </div>
    </div>
</template>