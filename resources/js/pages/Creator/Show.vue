<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Head, Link } from '@inertiajs/vue3';
import {
    MapPin,
    Calendar,
    Star,
    BookOpen,
    Heart,
    MessageSquare,
    Eye,
    UserPlus,
    UserMinus,
    ArrowLeft
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Creator {
    id: number;
    display_name: string;
    bio?: string;
    specialty: string;
    location?: string;
    website?: string;
    verification_status: string;
    rating?: number;
    followers_count?: number;
    following_count?: number;
    posts_count?: number;
    likes_received?: number;
    joined_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string;
        created_at: string;
    };
}

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    type: string;
    status: string;
    is_featured: boolean;
    is_premium: boolean;
    view_count: number;
    like_count: number;
    comment_count: number;
    images: string[];
    published_at: string;
    created_at: string;
    category: {
        id: number;
        name: string;
        slug: string;
        color: string;
    };
}

interface Props {
    creator: Creator;
    posts: {
        data: Post[];
        links: any;
        meta: any;
    };
    isFollowing: boolean;
    canFollow: boolean;
}

const props = defineProps<Props>();

const isFollowingState = ref(props.isFollowing);
const followersCount = ref(props.creator.followers_count || 0);
const isLoading = ref(false);

const postTypeLabels = {
    discussion: '讨论',
    tutorial: '教程',
    showcase: '展示',
    question: '问题'
};

const postTypeIcons = {
    discussion: '💬',
    tutorial: '📖',
    showcase: '🎨',
    question: '❓'
};

const verificationBadge = computed(() => {
    switch (props.creator.verification_status) {
        case 'verified':
            return { text: '已认证', class: 'bg-green-500/20 text-green-400 border-green-500/30' };
        case 'premium':
            return { text: 'VIP创作者', class: 'bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30' };
        case 'expert':
            return { text: '专家', class: 'bg-purple-500/20 text-purple-400 border-purple-500/30' };
        default:
            return null;
    }
});

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getInitials(name: string) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
}

const toggleFollow = async () => {
    if (!props.canFollow || isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await fetch(`/creators/${props.creator.id}/follow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok && data.success) {
            isFollowingState.value = data.following;
            followersCount.value = data.followers_count;
        } else {
            console.error('Follow toggle failed:', data.message || 'Unknown error');
            alert(data.message || '操作失败，请重试');
        }
    } catch (error) {
        console.error('Error toggling follow:', error);
        alert('网络错误，请重试');
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <WebLayout>
        <Head :title="`${creator.display_name} - 创作者档案`" />

        <div class="min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Header -->
                <div class="flex items-center mb-8">
                    <Link href="/creators" class="mr-4">
                        <Button variant="ghost" size="sm" class="text-white hover:text-[#ff6e02]">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            返回创作者
                        </Button>
                    </Link>
                </div>

                <!-- Creator Profile -->
                <Card class="bg-[#374151] border-[#4B5563] mb-8">
                    <CardContent class="p-8">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Avatar Section -->
                            <div class="flex-shrink-0">
                                <Avatar class="h-32 w-32 mx-auto md:mx-0">
                                    <AvatarImage
                                        v-if="creator.user.avatar"
                                        :src="creator.user.avatar"
                                        :alt="creator.display_name"
                                    />
                                    <AvatarFallback class="bg-[#ff6e02] text-white text-2xl font-bold">
                                        {{ getInitials(creator.display_name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </div>

                            <!-- Profile Info -->
                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-4">
                                    <div>
                                        <h1 class="text-3xl font-bold text-white mb-2 flex items-center justify-center md:justify-start gap-3">
                                            {{ creator.display_name }}
                                            <Badge v-if="verificationBadge" :class="verificationBadge.class">
                                                {{ verificationBadge.text }}
                                            </Badge>
                                        </h1>
                                        <p class="text-[#ff6e02] font-medium mb-2">{{ creator.specialty }}</p>
                                        <div class="flex items-center justify-center md:justify-start text-[#999999] text-sm gap-4 mb-4">
                                            <span v-if="creator.location" class="flex items-center gap-1">
                                                <MapPin class="h-4 w-4" />
                                                {{ creator.location }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Calendar class="h-4 w-4" />
                                                {{ formatDate(creator.joined_at || creator.user.created_at) }} 加入
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Follow Button -->
                                    <div v-if="canFollow" class="mt-4 md:mt-0">
                                        <Button
                                            @click="toggleFollow"
                                            :disabled="isLoading"
                                            :class="isFollowingState
                                                ? 'bg-transparent border-[#ff6e02] text-[#ff6e02] hover:bg-[#ff6e02] hover:text-white'
                                                : 'bg-[#ff6e02] hover:bg-[#e55a00] text-white'"
                                        >
                                            <UserPlus v-if="!isFollowingState" class="h-4 w-4 mr-2" />
                                            <UserMinus v-else class="h-4 w-4 mr-2" />
                                            {{ isLoading ? '处理中...' : (isFollowingState ? '取消关注' : '关注') }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Bio -->
                                <p v-if="creator.bio" class="text-[#cccccc] mb-6 max-w-2xl">
                                    {{ creator.bio }}
                                </p>

                                <!-- Stats -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div class="text-center p-4 bg-[#1f2937] rounded-lg">
                                        <div class="text-2xl font-bold text-white">{{ followersCount }}</div>
                                        <div class="text-sm text-[#999999]">粉丝</div>
                                    </div>
                                    <div class="text-center p-4 bg-[#1f2937] rounded-lg">
                                        <div class="text-2xl font-bold text-white">{{ creator.posts_count || 0 }}</div>
                                        <div class="text-sm text-[#999999]">帖子</div>
                                    </div>
                                    <div class="text-center p-4 bg-[#1f2937] rounded-lg">
                                        <div class="text-2xl font-bold text-white">{{ creator.likes_received || 0 }}</div>
                                        <div class="text-sm text-[#999999]">获赞</div>
                                    </div>
                                    <div v-if="creator.rating" class="text-center p-4 bg-[#1f2937] rounded-lg">
                                        <div class="text-2xl font-bold text-white flex items-center justify-center gap-1">
                                            {{ creator.rating }}
                                            <Star class="h-5 w-5 text-yellow-400 fill-current" />
                                        </div>
                                        <div class="text-sm text-[#999999]">评分</div>
                                    </div>
                                </div>

                                <!-- Website -->
                                <div v-if="creator.website" class="text-center md:text-left">
                                    <a
                                        :href="creator.website"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-[#ff6e02] hover:text-[#e55a00] underline"
                                    >
                                        {{ creator.website }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Posts Section -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                        <BookOpen class="h-7 w-7 text-[#ff6e02]" />
                        最新帖子
                    </h2>

                    <div v-if="posts.data.length === 0" class="text-center py-16">
                        <BookOpen class="h-16 w-16 text-[#666666] mx-auto mb-6" />
                        <h3 class="text-xl font-medium text-white mb-4">暂无发布内容</h3>
                        <p class="text-[#999999] max-w-md mx-auto">
                            该创作者还没有发布任何内容，请稍后再来查看
                        </p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Card
                            v-for="post in posts.data"
                            :key="post.id"
                            class="bg-[#374151] border-[#4B5563] hover:border-[#6B7280] transition-all duration-200 hover:scale-105 group overflow-hidden"
                        >
                            <!-- Post Image -->
                            <div v-if="post.images && post.images.length > 0" class="aspect-video overflow-hidden">
                                <img
                                    :src="post.images[0]"
                                    :alt="post.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />
                            </div>

                            <CardContent class="p-6">
                                <!-- Post Type & Category -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">{{ postTypeIcons[post.type] }}</span>
                                        <Badge class="bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30">
                                            {{ postTypeLabels[post.type] }}
                                        </Badge>
                                    </div>
                                    <Badge v-if="post.is_featured" class="bg-yellow-500/20 text-yellow-400 border-yellow-500/30">
                                        精选
                                    </Badge>
                                </div>

                                <!-- Post Title -->
                                <Link :href="`/posts/${post.slug}`">
                                    <h3 class="text-lg font-semibold text-white hover:text-[#ff6e02] transition-colors mb-3 line-clamp-2">
                                        {{ post.title }}
                                    </h3>
                                </Link>

                                <!-- Post Excerpt -->
                                <p v-if="post.excerpt" class="text-[#999999] text-sm mb-4 line-clamp-3">
                                    {{ post.excerpt }}
                                </p>

                                <!-- Category -->
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-3 h-3 rounded-full bg-[#ff6e02]"></div>
                                    <span class="text-sm text-[#999999]">{{ post.category.name }}</span>
                                </div>

                                <!-- Engagement Stats -->
                                <div class="flex items-center justify-between text-sm text-[#999999] pt-4 border-t border-[#4B5563]">
                                    <div class="flex items-center gap-4">
                                        <span class="flex items-center gap-1">
                                            <Eye class="h-3 w-3" />
                                            {{ post.view_count }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Heart class="h-3 w-3" />
                                            {{ post.like_count }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <MessageSquare class="h-3 w-3" />
                                            {{ post.comment_count }}
                                        </span>
                                    </div>
                                    <span class="text-xs">
                                        {{ formatDate(post.published_at || post.created_at) }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Pagination would go here -->
                    <div v-if="posts.data.length > 0" class="mt-12 flex justify-center">
                        <p class="text-[#999999] text-sm">
                            显示 {{ posts.data.length }} 篇帖子
                            <span v-if="posts.meta?.total"> / 共 {{ posts.meta.total }} 篇</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>