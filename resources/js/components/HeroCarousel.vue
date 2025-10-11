<script setup lang="ts">
import 'vue3-carousel/dist/carousel.css';
import { Carousel, Navigation, Pagination, Slide } from 'vue3-carousel';

interface CarouselSlide {
    title: string;
    description: string;
    emoji: string;
    bgGradient: string;
}

const slides: CarouselSlide[] = [
    {
        emoji: '👨‍🍳',
        title: '欢迎来到美味论坛',
        description: '发现精彩菜谱，向大厨学习，与热爱美食的朋友们分享你的烹饪创意',
        bgGradient: 'from-[#ff6e02]/20 to-[#1c1c1c]',
    },
    {
        emoji: '🍳',
        title: '精选大厨菜谱',
        description: '探索来自专业厨师和家庭烹饪高手的数千道精美菜谱',
        bgGradient: 'from-[#10b981]/20 to-[#1c1c1c]',
    },
    {
        emoji: '⭐',
        title: '加入美食社区',
        description: '分享你的拿手菜，积累粉丝，成为社区认可的美食达人',
        bgGradient: 'from-[#8b5cf6]/20 to-[#1c1c1c]',
    },
];
</script>

<template>
    <div class="relative w-full">
        <Carousel
            :autoplay="5000"
            :wrap-around="true"
            :transition="500"
        >
            <Slide v-for="(slide, index) in slides" :key="index">
                <div
                    class="flex w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br p-12 text-center"
                    :class="slide.bgGradient"
                >
                    <div class="mb-6 text-6xl">{{ slide.emoji }}</div>
                    <h2 class="mb-4 text-3xl font-bold text-white sm:text-4xl">
                        {{ slide.title }}
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-[#999999]">
                        {{ slide.description }}
                    </p>
                </div>
            </Slide>

            <template #addons>
                <Navigation />
                <Pagination />
            </template>
        </Carousel>
    </div>
</template>

<style scoped>
:deep(.carousel__prev),
:deep(.carousel__next) {
    background-color: rgba(255, 110, 2, 0.8);
    border-radius: 0.25rem;
    width: 2.5rem;
    height: 2.5rem;
    color: white;
}

:deep(.carousel__prev:hover),
:deep(.carousel__next:hover) {
    background-color: rgba(255, 110, 2, 1);
}

:deep(.carousel__pagination-button) {
    background-color: rgba(153, 153, 153, 0.5);
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

:deep(.carousel__pagination-button--active) {
    background-color: #ff6e02;
}
</style>
