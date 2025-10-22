<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ChefHat, Heart, Eye, MessageSquare, ArrowRight, Star, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
        creator_profile?: {
            id: number;
            display_name: string;
            specialty: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
        icon?: string;
    };
    view_count: number;
    like_count: number;
    comment_count: number;
    published_at: string;
    first_image?: {
        url: string;
        thumb: string;
    } | null;
    post_images?: Array<{
        url: string;
        thumb: string;
    }>;
}

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    rating: number;
    follower_count: number;
    avatar?: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon: string;
    posts_count: number;
    nav_route: string;
}

interface Props {
    featuredPosts: Post[];
    recentPosts: Post[];
    featuredCreators: Creator[];
    popularCategories: Category[];
    categories: Category[];
}

const props = defineProps<Props>();

// Banner slides using static images from /public/slides
const bannerSlides = [
    {
        image: '/slides/slide1.png',
        title: '属于餐饮人的学习社区'
    },
    {
        image: '/slides/slide2.png',
        title: '分享美食，交流技艺'
    },
    {
        image: '/slides/slide3.png',
        title: '共同成长，创造价值'
    }
];

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diff < 60) return '刚刚';
    if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}天前`;

    return date.toLocaleDateString('zh-CN');
};
</script>

<template>
    <WebLayout>
        <!-- Hero Banner Section with Carousel -->
        <section class="bg-background border-b border-[#333333]">
            <div class="max-w-[1000px] mx-auto py-6 lg:py-12 px-4">
                <div class="relative rounded-xl overflow-hidden shadow-lg" style="height: 350px;">
                    <Carousel
                        v-if="bannerSlides.length > 0"
                        :autoplay="5000"
                        :wrap-around="true"
                        :transition="500"
                    >
                        <Slide v-for="(slide, index) in bannerSlides" :key="index">
                            <div class="relative w-full h-[350px]">
                                <img
                                    :src="slide.image"
                                    class="w-full h-full object-cover"
                                    :alt="slide.title"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                                    <h2 class="text-2xl lg:text-4xl font-bold mb-2">{{ slide.title }}</h2>
                                </div>
                            </div>
                        </Slide>

                        <template #addons>
                            <Pagination />
                        </template>
                    </Carousel>
                </div>
            </div>
        </section>


        <!-- 赚钱大佬推荐 -->
        <section class="py-12 bg-background">
            <div class="max-w-[1000px] mx-auto px-4">
                
                <div class="grid grid-cols-4 gap-6">
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="category.nav_route || '/'"
                        class="flex flex-col items-center justify-center hover:bg-accent rounded-lg py-4 transition cursor-pointer group"
                       
                    >
                        <div class="w-14 h-14 mb-2 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden shadow group-hover:bg-[#fffaee]">
                            <!-- <img
                                v-if="category.icon"
                                :src="category.icon"
                                :alt="category.name"
                                class="w-8 h-8 object-contain"
                            /> -->
                            <span  class="text-2xl">{{ category.icon || '📁' }}</span>
                        </div>
                        <div class="text-center text-sm text-white font-medium mt-1 truncate max-w-[64px]">
                            {{ category.name }}
                        </div>
                    </Link>
                </div>
                <!-- End Categories Grid -->
            </div>
        </section>

        <!-- Recommended Creators Section -->
        <section class="py-12 bg-background">
            <div class="max-w-[1000px] mx-auto px-4">
                <!-- Section Title -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-foreground flex items-center gap-2">
                        <Star class="h-6 w-6 text-[#ff6e02]" fill="#ff6e02" />
                        推荐博主
                    </h2>
                    <Link href="/community/creators" class="text-[#ff6e02] hover:underline flex items-center gap-1 text-sm">
                        更多
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Creators Horizontal Scroll -->
                <div class="flex gap-6 overflow-x-auto pb-4 scrollbar-hide">
                    <Link
                        v-for="creator in featuredCreators"
                        :key="creator.id"
                        :href="`/creators/${creator.id}`"
                        class="flex-shrink-0 w-24 group"
                    >
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-3">
                            <img
                                :src="creator.user.avatar || `https://ui-avatars.com/api/?name=${creator.display_name}&size=130`"
                                class="w-full h-18 object-cover rounded-lg mb-3"
                                :alt="creator.display_name"
                            />
                            <p class="text-sm text-center text-foreground font-medium truncate">
                                {{ creator.display_name }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Hot Posts Section -->
        <section class="py-12 bg-background">
            <div class="max-w-[1000px] mx-auto px-4">
                <!-- Section Title -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-foreground flex items-center gap-2">
                        <TrendingUp class="h-6 w-6 text-[#ff6e02]" />
                        热门帖子
                    </h2>
                    <Link href="/community/posts" class="text-[#ff6e02] hover:underline flex items-center gap-1 text-sm">
                        更多
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Posts List -->
                <div class="space-y-4">
                    <Link
                        v-for="post in recentPosts"
                        :key="post.id"
                        :href="`/posts/${post.slug}`"
                        class="block rounded-lg shadow hover:shadow-md transition-shadow p-5"
                    >
                        <!-- Post Title -->
                        <h3 class="text-base lg:text-lg font-semibold text-foreground mb-3 hover:text-[#ff6e02] transition-colors line-clamp-2">
                            {{ post.title }}
                        </h3>

                        <!-- Post Content -->
                        <p class="font24 color999 line-clamp-3 u-m-b-15">
                            {{ post.excerpt }}
                        </p>

                        <!-- Post Images (First 4) -->
                        <div v-if="post.post_images && post.post_images.length > 0" class="flex gap-2 mb-4">
                            <div
                                v-for="(image, index) in post.post_images.slice(0, 4)"
                                :key="index"
                                class="relative overflow-hidden rounded-lg w-20 h-20 flex-shrink-0"
                            >
                                <img
                                    :src="image.thumb || image.url"
                                    class="w-full h-full object-cover"
                                    :alt="`${post.title} - Image ${index + 1}`"
                                />
                            </div>
                        </div>

                        <!-- Post Footer -->
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-4">
                                <!-- Avatar -->
                                <div class="flex items-center gap-2">
                                    <img
                                        :src="post.user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.creator_profile?.display_name || post.user.name)}&size=32&background=ff6e02&color=fff`"
                                        class="w-8 h-8 rounded-full object-cover"
                                        :alt="post.user.name"
                                        @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}&size=32&background=ff6e02&color=fff`"
                                    />
                                    <span class="text-foreground">
                                        {{ post.user.creator_profile?.display_name || post.user.name }}
                                    </span>
                                </div>

                                <!-- Time -->
                                <span class="text-muted-foreground">
                                    {{ formatTime(post.published_at) }}
                                </span>
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 text-muted-foreground">
                                <span class="flex items-center gap-1">
                                    <Eye class="h-4 w-4" />
                                    {{ post.view_count }}
                                </span>
                                <span class="flex items-center gap-1 hover:text-[#ff6e02] transition-colors">
                                    <Heart class="h-4 w-4" />
                                    {{ post.like_count }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-12 ">
            <div class="max-w-full mx-auto px-4">
                <h2 class=" lg:text-2xl bg-zinc-800 text-white py-2 rounded-lg  text-center font-bold  mb-6">分类显示</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <Link
                        v-for="category in popularCategories"
                        :key="category.id"
                        :href="`/community/posts?category=${category.slug}`"
                        class="flex items-center gap-3 p-2 bg-background rounded-lg hover:bg-gray-500 transition-colors"
                    >
                        <div class="text-3xl">{{ category.icon || '📝' }}</div>
                        <div>
                            <p class="font-medium text-foreground">{{ category.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ category.posts_count }} 帖子</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </WebLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Carousel dark theme styling */
:deep(.carousel__prev),
:deep(.carousel__next) {
    background-color: rgba(255, 110, 2, 0.8);
    border-radius: 50%;
    width: 40px;
    height: 40px;
}

:deep(.carousel__prev):hover,
:deep(.carousel__next):hover {
    background-color: rgba(255, 110, 2, 1);
}

:deep(.carousel__pagination-button) {
    background-color: rgba(255, 255, 255, 0.5);
}

:deep(.carousel__pagination-button--active) {
    background-color: #ff6e02;
}
</style>
