<script setup lang="ts">
import WebHeader from '@/components/WebHeader.vue';
import WebFooter from '@/components/WebFooter.vue';
import MobileTabBar from '@/components/MobileTabBar.vue';
</script>

<template>
    <div class="min-h-screen bg-[#1c1c1c] text-white">
        <!-- Header -->
        <WebHeader />

        <!-- Main Content -->
        <main class="pb-0 lg:pb-0">
            <slot />
        </main>

        <!-- Footer - Hidden on mobile, shown on desktop -->
        <div class="hidden lg:block">
            <WebFooter />
        </div>

        <!-- Mobile Tab Bar - Shown only on mobile -->
        <MobileTabBar />
    </div>
</template>