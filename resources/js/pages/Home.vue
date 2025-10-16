<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ChefHat, Heart, Eye, MessageSquare, ArrowRight, Star, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

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
}

interface Props {
    featuredPosts: Post[];
    recentPosts: Post[];
    featuredCreators: Creator[];
    popularCategories: Category[];
}

const props = defineProps<Props>();

// Banner slides from featured posts
const bannerSlides = computed(() => {
    return props.featuredPosts.slice(0, 3).map(post => ({
        image: post.first_image?.url || '/placeholder.jpg',
        title: post.title,
        link: `/posts/${post.slug}`
    }));
});

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
        <!-- Hero Banner Section -->
        <section class="bg-white border-b border-gray-200">
            <div class="max-w-[1000px] mx-auto py-12 px-4">
                <div class="relative rounded-xl overflow-hidden shadow-lg" style="height: 350px;">
                    <!-- Simple banner with first featured post -->
                    <div v-if="featuredPosts.length > 0" class="relative w-full h-full">
                        <img
                            :src="featuredPosts[0].first_image?.url || '/placeholder.jpg'"
                            class="w-full h-full object-cover"
                            alt="Banner"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                            <h1 class="text-4xl font-bold mb-2">属于餐饮人的学习社区</h1>
                            <p class="text-lg opacity-90">分享美食，交流技艺，共同成长</p>
                        </div>
                    </div>
                </div>
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
                        class="flex-shrink-0 w-[130px] group"
                    >
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-3">
                            <img
                                :src="creator.user.avatar || `https://ui-avatars.com/api/?name=${creator.display_name}&size=130`"
                                class="w-full h-[160px] object-cover rounded-lg mb-3"
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
                        class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow p-5"
                    >
                        <!-- Post Title -->
                        <h3 class="text-lg font-semibold text-foreground mb-3 hover:text-[#ff6e02] transition-colors line-clamp-2">
                            {{ post.title }}
                        </h3>

                        <!-- Post Images (if available) -->
                        <div v-if="post.first_image" class="mb-4">
                            <img
                                :src="post.first_image.thumb"
                                class="w-full h-[200px] object-cover rounded-lg"
                                :alt="post.title"
                            />
                        </div>

                        <!-- Post Footer -->
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-4">
                                <!-- Avatar -->
                                <div class="flex items-center gap-2">
                                    <img
                                        :src="post.user.avatar || `https://ui-avatars.com/api/?name=${post.user.name}&size=32`"
                                        class="w-8 h-8 rounded-full"
                                        :alt="post.user.name"
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
        <section class="py-12 bg-white border-t border-gray-200">
            <div class="max-w-[1000px] mx-auto px-4">
                <h2 class="text-2xl font-bold text-foreground mb-6">热门分类</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link
                        v-for="category in popularCategories"
                        :key="category.id"
                        :href="`/community/posts?category=${category.slug}`"
                        class="flex items-center gap-3 p-4 bg-background rounded-lg hover:bg-gray-100 transition-colors"
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
</style>
