<script setup lang="ts">
import WebHeader from '@/components/WebHeader.vue';
import WebFooter from '@/components/WebFooter.vue';
</script>

<template>
    <div class="min-h-screen bg-[#1c1c1c] text-white">
        <!-- Header -->
        <WebHeader />

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <WebFooter />
    </div>
</template>