<script setup lang="ts">
import ChineseLayout from '@/layouts/ChineseLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Link } from '@inertiajs/vue3';
import {
    ChefHat,
    Users,
    BookOpen,
    Star,
    ArrowRight,
    Crown,
    Heart,
    Eye,
    MessageSquare,
    Award,
    Utensils,
    Clock,
    TrendingUp
} from 'lucide-vue-next';
import PostCard from '@/components/PostCard.vue';
import CreatorCard from '@/components/CreatorCard.vue';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        creator_profile?: {
            display_name: string;
            specialty: string;
            verification_status: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
    };
    type: string;
    view_count: number;
    like_count: number;
    is_premium: boolean;
    published_at: string;
}

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    rating: number;
    follower_count: number;
    is_featured: boolean;
    verification_status: string;
    user: {
        id: number;
        name: string;
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

interface Testimonial {
    id: number;
    content: string;
    author: string;
    rating: number;
    specialty: string;
}

interface Stats {
    total_recipes: number;
    total_chefs: number;
    total_cuisines: number;
    total_members: number;
}

interface Props {
    featuredPosts: Post[];
    recentPosts: Post[];
    featuredCreators: Creator[];
    popularCategories: Category[];
    stats: Stats;
    testimonials: Testimonial[];
}

defineProps<Props>();

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const features = [
    {
        icon: BookOpen,
        title: 'Discover Recipes',
        description: 'Explore thousands of recipes from professional chefs and home cooks worldwide.'
    },
    {
        icon: Users,
        title: 'Learn from Masters',
        description: 'Connect with verified culinary experts and learn their secret techniques.'
    },
    {
        icon: Crown,
        title: 'Premium Content',
        description: 'Access exclusive recipes and advanced cooking tutorials with VIP membership.'
    },
    {
        icon: Award,
        title: 'Build Your Reputation',
        description: 'Share your recipes, gain followers, and become a recognized chef in the community.'
    }
];
</script>

<template>
    <ChineseLayout>
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-[#1c1c1c]">
            <div class="max-w-[1000px] mx-auto px-4 py-12">
                <div class="text-center">
                    <div class="flex justify-center mb-6">
                        <div class="text-6xl">👨‍🍳</div>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-bold mb-6 leading-tight text-white">
                        欢迎来到<br>
                        <span class="text-[#ff6e02]">华厨社区</span>
                    </h1>

                    <p class="text-lg mb-8 text-[#999999] max-w-2xl mx-auto leading-relaxed">
                        发现精彩菜谱，向大厨学习，与热爱美食的朋友们分享你的烹饪创意
                    </p>

                    <!-- Hero Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 max-w-3xl mx-auto">
                        <div class="bg-[#374151] rounded-lg p-4">
                            <div class="text-xl font-bold text-white">{{ stats.total_recipes.toLocaleString() }}+</div>
                            <div class="text-sm text-[#999999]">菜谱</div>
                        </div>
                        <div class="bg-[#374151] rounded-lg p-4">
                            <div class="text-xl font-bold text-white">{{ stats.total_chefs.toLocaleString() }}+</div>
                            <div class="text-sm text-[#999999]">大厨</div>
                        </div>
                        <div class="bg-[#374151] rounded-lg p-4">
                            <div class="text-xl font-bold text-white">{{ stats.total_cuisines }}+</div>
                            <div class="text-sm text-[#999999]">菜系</div>
                        </div>
                        <div class="bg-[#374151] rounded-lg p-4">
                            <div class="text-xl font-bold text-white">{{ stats.total_members.toLocaleString() }}+</div>
                            <div class="text-sm text-[#999999]">会员</div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Button size="lg" class="bg-[#ff6e02] text-white hover:bg-[#e55a00] text-base px-6 py-3" as-child>
                            <Link href="/community">
                                <Utensils class="w-4 h-4 mr-2" />
                                浏览菜谱
                            </Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            class="text-[#ff6e02] border-[#ff6e02] hover:bg-[#ff6e02] hover:text-white text-base px-6 py-3"
                            as-child
                        >
                            <Link href="/creators/apply">
                                <ChefHat class="w-4 h-4 mr-2" />
                                申请认证
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Categories Section -->
        <section class="py-12 px-4">
            <div class="max-w-[1000px] mx-auto">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-[#ff6e02] text-white px-4 py-2 rounded text-base font-medium">
                            技术交流区
                        </div>
                        <Link href="/community/posts" class="text-[#ff6e02] text-sm hover:underline flex items-center">
                            更多 >>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link
                        v-for="category in popularCategories.slice(0, 8)"
                        :key="category.id"
                        :href="`/community/posts?category=${category.slug}`"
                        class="group"
                    >
                        <div class="bg-[#374151] rounded-lg p-4 text-center hover:bg-[#1f2937] transition-colors">
                            <div class="text-3xl mb-2">{{ category.icon || '🍽️' }}</div>
                            <h3 class="text-white text-sm font-medium mb-1">{{ category.name }}</h3>
                            <div class="text-[#999999] text-xs">{{ category.posts_count }} 个菜谱</div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Recipes Section -->
        <section v-if="featuredPosts.length > 0" class="py-12 px-4 bg-[#1c1c1c]">
            <div class="max-w-[1000px] mx-auto">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-[#ff6e02] text-white px-4 py-2 rounded text-base font-medium">
                            热门帖子
                        </div>
                        <Link href="/community/posts" class="text-[#ff6e02] text-sm hover:underline flex items-center">
                            更多 >>
                        </Link>
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-for="post in featuredPosts.slice(0, 6)" :key="post.id" class="bg-[#374151] rounded-lg p-4 hover:bg-[#1f2937] transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-16 bg-[#1f2937] rounded-lg flex-shrink-0 flex items-center justify-center">
                                <div class="text-2xl">🍳</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <Link :href="`/posts/${post.slug}`" class="text-white hover:text-[#ff6e02] font-medium text-base line-clamp-2 mb-2 block">
                                    {{ post.title }}
                                </Link>
                                <p class="text-[#999999] text-sm line-clamp-2 mb-3">{{ post.excerpt }}</p>
                                <div class="flex items-center justify-between text-xs text-[#999999]">
                                    <div class="flex items-center gap-4">
                                        <span>{{ post.user.creator_profile?.display_name || post.user.name }}</span>
                                        <span>{{ new Date(post.published_at).toLocaleDateString('zh-CN') }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1">
                                            <Eye class="w-3 h-3" />
                                            {{ post.view_count }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Heart class="w-3 h-3" />
                                            {{ post.like_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Chefs Section -->
        <section v-if="featuredCreators.length > 0" class="py-12 px-4 bg-[#1c1c1c]">
            <div class="max-w-[1000px] mx-auto">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-[#ff6e02] text-white px-4 py-2 rounded text-base font-medium">
                            推荐博主
                        </div>
                        <Link href="/community/creators" class="text-[#ff6e02] text-sm hover:underline flex items-center">
                            更多 >>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link
                        v-for="creator in featuredCreators.slice(0, 8)"
                        :key="creator.id"
                        :href="`/creators/${creator.id}`"
                        class="group"
                    >
                        <div class="bg-[#374151] rounded-lg p-4 text-center hover:bg-[#1f2937] transition-colors">
                            <div class="w-12 h-12 bg-[#1f2937] rounded-full flex items-center justify-center mx-auto mb-3">
                                <div class="text-lg">👨‍🍳</div>
                            </div>
                            <h3 class="text-white text-sm font-medium mb-1">{{ creator.display_name }}</h3>
                            <div class="text-[#999999] text-xs mb-2">{{ creator.specialty }}</div>
                            <div class="flex items-center justify-center gap-2 text-xs">
                                <span class="text-[#ff6e02]">⭐ {{ creator.rating }}</span>
                                <span class="text-[#999999]">{{ creator.follower_count }} 粉丝</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Recent Posts Section -->
        <section v-if="recentPosts.length > 0" class="py-12 px-4 bg-[#1c1c1c]">
            <div class="max-w-[1000px] mx-auto">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-[#ff6e02] text-white px-4 py-2 rounded text-base font-medium">
                            网站自制教学板块
                        </div>
                        <Link href="/community/posts" class="text-[#ff6e02] text-sm hover:underline flex items-center">
                            更多 >>
                        </Link>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="post in recentPosts.slice(0, 8)" :key="post.id" class="bg-[#374151] rounded-lg p-3 hover:bg-[#1f2937] transition-colors">
                        <div class="flex items-center justify-between">
                            <Link :href="`/posts/${post.slug}`" class="text-white hover:text-[#ff6e02] text-sm font-medium line-clamp-1 flex-1 mr-4">
                                {{ post.title }}
                            </Link>
                            <div class="flex items-center gap-4 text-xs text-[#999999] flex-shrink-0">
                                <span>{{ post.user.creator_profile?.display_name || post.user.name }}</span>
                                <span>{{ new Date(post.published_at).toLocaleDateString('zh-CN') }}</span>
                                <span class="flex items-center gap-1">
                                    <Eye class="w-3 h-3" />
                                    {{ post.view_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ChineseLayout>
</template>