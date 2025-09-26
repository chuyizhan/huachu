<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Heart,
    Eye,
    MessageSquare,
    Share2,
    Bookmark,
    User,
    Tag,
    ChefHat,
    Star,
    Calendar,
    ArrowLeft,
    ThumbsUp
} from 'lucide-vue-next';

interface Creator {
    id: number;
    display_name: string;
    specialty: string;
    verification_status: string;
    rating?: number;
    follower_count?: number;
}

interface User {
    id: number;
    name: string;
    creator_profile?: Creator;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    is_nav_item?: boolean;
    nav_route?: string;
}

interface Post {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    slug: string;
    type: string;
    status: string;
    is_featured: boolean;
    is_premium: boolean;
    view_count: number;
    like_count: number;
    comment_count: number;
    share_count: number;
    tags: string[];
    published_at: string;
    created_at: string;
    user: User;
    category: Category;
}

interface Props {
    post: Post;
    relatedPosts: Post[];
    userInteractions: {
        liked: boolean;
        favorited: boolean;
    };
}

const props = defineProps<Props>();
const page = usePage();

// Reactive state for like functionality
const isLiked = ref(props.userInteractions.liked);
const likeCount = ref(props.post.like_count);
const isLiking = ref(false);

// Reactive state for favorite functionality
const isFavorited = ref(props.userInteractions.favorited);
const isFavoriting = ref(false);

// Check if user is authenticated
const isAuthenticated = computed(() => page.props.auth?.user);

// Toggle like function
const toggleLike = async () => {
    if (!isAuthenticated.value) {
        // Redirect to login or show login modal
        window.location.href = '/login';
        return;
    }

    if (isLiking.value) return; // Prevent multiple clicks

    isLiking.value = true;

    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(`/posts/${props.post.id}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok && data.success) {
            isLiked.value = data.liked;
            likeCount.value = data.like_count;
        } else {
            console.error('Failed to toggle like:', data.message);
            // Show user-friendly error message
            alert(data.message || 'Failed to process like. Please try again.');
        }
    } catch (error) {
        console.error('Error toggling like:', error);
        alert('Network error. Please check your connection and try again.');
    } finally {
        isLiking.value = false;
    }
};

// Toggle favorite function
const toggleFavorite = async () => {
    if (!isAuthenticated.value) {
        // Redirect to login or show login modal
        window.location.href = '/login';
        return;
    }

    if (isFavoriting.value) return; // Prevent multiple clicks

    isFavoriting.value = true;

    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(`/posts/${props.post.id}/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok && data.success) {
            isFavorited.value = data.favorited;
        } else {
            console.error('Failed to toggle favorite:', data.message);
            // Show user-friendly error message
            alert(data.message || '收藏操作失败，请重试');
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
        alert('网络错误，请检查网络连接后重试');
    } finally {
        isFavoriting.value = false;
    }
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getPostTypeText = (type: string) => {
    const typeMap = {
        'tutorial': '教程',
        'discussion': '讨论',
        'showcase': '展示',
        'question': '问答'
    };
    return typeMap[type as keyof typeof typeMap] || type;
};
</script>

<template>
    <WebLayout>
        <div class="max-w-[1000px] mx-auto px-4 py-6">
            <!-- Back Button -->
            <div class="mb-6">
                <Link href="/" class="inline-flex items-center text-[#ff6e02] hover:text-[#e55a00] text-sm">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    返回首页
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Post Header -->
                    <div class="bg-[#374151] rounded-lg p-6 mb-6">
                        <!-- Category & Type Badge -->
                        <div class="flex items-center gap-2 mb-4">
                            <Badge
                                class="text-white text-xs px-2 py-1"
                                :style="{ backgroundColor: post.category.color }"
                            >
                                {{ post.category.icon || '📝' }} {{ post.category.name }}
                            </Badge>
                            <Badge variant="outline" class="text-[#999999] border-[#999999] text-xs">
                                {{ getPostTypeText(post.type) }}
                            </Badge>
                            <Badge v-if="post.is_featured" class="bg-[#ff6e02] text-white text-xs">
                                精选
                            </Badge>
                            <Badge v-if="post.is_premium" class="bg-yellow-500 text-white text-xs">
                                ⭐ VIP
                            </Badge>
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-4 leading-tight">
                            {{ post.title }}
                        </h1>

                        <!-- Excerpt -->
                        <p v-if="post.excerpt" class="text-[#999999] text-base mb-6 leading-relaxed">
                            {{ post.excerpt }}
                        </p>

                        <!-- Author & Meta Info -->
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-3">
                                <Avatar class="w-10 h-10">
                                    <AvatarFallback class="bg-[#1f2937] text-white text-sm">
                                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-white font-medium">
                                            {{ post.user.creator_profile?.display_name || post.user.name }}
                                        </span>
                                        <ChefHat v-if="post.user.creator_profile?.verification_status === 'verified'"
                                                class="w-4 h-4 text-[#ff6e02]" />
                                    </div>
                                    <div class="text-xs text-[#999999]">
                                        {{ post.user.creator_profile?.specialty || '美食爱好者' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-[#999999]">
                                <span class="flex items-center gap-1">
                                    <Calendar class="w-3 h-3" />
                                    {{ formatDate(post.published_at) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Eye class="w-3 h-3" />
                                    {{ post.view_count }}
                                </span>
                            </div>
                        </div>

                        <!-- Engagement Stats -->
                        <div class="flex items-center justify-between border-t border-[#1f2937] pt-4">
                            <div class="flex items-center gap-6">
                                <span class="flex items-center gap-1 text-sm text-[#999999]">
                                    <Heart class="w-4 h-4" />
                                    {{ likeCount }} 赞
                                </span>
                                <span class="flex items-center gap-1 text-sm text-[#999999]">
                                    <MessageSquare class="w-4 h-4" />
                                    {{ post.comment_count }} 评论
                                </span>
                                <span class="flex items-center gap-1 text-sm text-[#999999]">
                                    <Share2 class="w-4 h-4" />
                                    {{ post.share_count }} 分享
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <Button
                                    size="sm"
                                    @click="toggleLike"
                                    :disabled="isLiking"
                                    :class="[
                                        'text-xs border transition-all duration-200 transform',
                                        isLiked
                                            ? 'bg-[#ff6e02] text-white border-[#ff6e02] hover:bg-[#e55a00] shadow-md'
                                            : 'bg-transparent text-[#ff6e02] border-[#ff6e02] hover:bg-[#ff6e02] hover:text-white',
                                        isLiking ? 'scale-95 opacity-75' : 'hover:scale-105'
                                    ]">
                                    <Heart
                                        :class="[
                                            'w-3 h-3 mr-1 transition-all duration-200',
                                            isLiked ? 'fill-current animate-pulse' : '',
                                            isLiking ? 'animate-spin' : ''
                                        ]"
                                    />
                                    {{ isLiking ? '处理中...' : (isLiked ? '已赞' : '点赞') }}
                                </Button>
                                <Button
                                    size="sm"
                                    @click="toggleFavorite"
                                    :disabled="isFavoriting"
                                    :class="[
                                        'text-xs border transition-all duration-200 transform',
                                        isFavorited
                                            ? 'bg-[#ff6e02] text-white border-[#ff6e02] hover:bg-[#e55a00] shadow-md'
                                            : 'bg-transparent text-[#ff6e02] border-[#ff6e02] hover:bg-[#ff6e02] hover:text-white',
                                        isFavoriting ? 'scale-95 opacity-75' : 'hover:scale-105'
                                    ]">
                                    <Bookmark
                                        :class="[
                                            'w-3 h-3 mr-1 transition-all duration-200',
                                            isFavorited ? 'fill-current animate-pulse' : '',
                                            isFavoriting ? 'animate-spin' : ''
                                        ]"
                                    />
                                    {{ isFavoriting ? '处理中...' : (isFavorited ? '已收藏' : '收藏') }}
                                </Button>
                                <Button size="sm"
                                        class="text-xs border bg-transparent text-[#ff6e02] border-[#ff6e02] hover:bg-[#ff6e02] hover:text-white">
                                    <Share2 class="w-3 h-3 mr-1" />
                                    分享
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Post Content -->
                    <Card class="bg-[#374151] border-0 mb-6">
                        <CardContent class="p-6">
                            <div class="prose prose-invert max-w-none">
                                <div class="text-white leading-relaxed whitespace-pre-line">
                                    {{ post.content }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tags -->
                    <div v-if="post.tags && post.tags.length > 0" class="mb-6">
                        <div class="flex items-center gap-2 flex-wrap">
                            <Tag class="w-4 h-4 text-[#999999]" />
                            <Badge v-for="tag in post.tags"
                                   :key="tag"
                                   variant="outline"
                                   class="text-[#999999] border-[#999999] hover:text-[#ff6e02] hover:border-[#ff6e02] cursor-pointer">
                                {{ tag }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <div v-if="relatedPosts.length > 0" class="bg-[#374151] rounded-lg p-6">
                        <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                            <ThumbsUp class="w-5 h-5 text-[#ff6e02]" />
                            相关推荐
                        </h3>
                        <div class="space-y-3">
                            <Link v-for="relatedPost in relatedPosts"
                                  :key="relatedPost.id"
                                  :href="`/posts/${relatedPost.slug}`"
                                  class="block group">
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-[#1f2937] transition-colors">
                                    <div class="w-16 h-12 bg-[#1f2937] rounded flex-shrink-0 flex items-center justify-center">
                                        <div class="text-lg">{{ relatedPost.category.icon || '📝' }}</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-white font-medium text-sm group-hover:text-[#ff6e02] transition-colors line-clamp-1 mb-1">
                                            {{ relatedPost.title }}
                                        </h4>
                                        <p class="text-[#999999] text-xs line-clamp-1 mb-2">
                                            {{ relatedPost.excerpt }}
                                        </p>
                                        <div class="flex items-center gap-3 text-xs text-[#999999]">
                                            <span>{{ relatedPost.user.creator_profile?.display_name || relatedPost.user.name }}</span>
                                            <span class="flex items-center gap-1">
                                                <Eye class="w-3 h-3" />
                                                {{ relatedPost.view_count }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Heart class="w-3 h-3" />
                                                {{ relatedPost.like_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Author Info Card -->
                    <Card class="bg-[#374151] border-0 mb-6">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-white text-base flex items-center gap-2">
                                <User class="w-4 h-4" />
                                作者信息
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center">
                                <Avatar class="w-16 h-16 mx-auto mb-3">
                                    <AvatarFallback class="bg-[#1f2937] text-white text-lg">
                                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="mb-2">
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        <span class="text-white font-medium">
                                            {{ post.user.creator_profile?.display_name || post.user.name }}
                                        </span>
                                        <ChefHat v-if="post.user.creator_profile?.verification_status === 'verified'"
                                                class="w-4 h-4 text-[#ff6e02]" />
                                    </div>
                                    <div class="text-xs text-[#999999] mb-2">
                                        {{ post.user.creator_profile?.specialty || '美食爱好者' }}
                                    </div>
                                    <div v-if="post.user.creator_profile" class="flex items-center justify-center gap-4 text-xs text-[#999999] mb-3">
                                        <span v-if="post.user.creator_profile.rating" class="flex items-center gap-1">
                                            <Star class="w-3 h-3 text-yellow-400" />
                                            {{ post.user.creator_profile.rating }}
                                        </span>
                                        <span v-if="post.user.creator_profile.follower_count">
                                            {{ post.user.creator_profile.follower_count }} 粉丝
                                        </span>
                                    </div>
                                </div>
                                <Button size="sm" class="bg-[#ff6e02] text-white hover:bg-[#e55a00] text-xs w-full">
                                    + 关注
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Category Info -->
                    <Card class="bg-[#374151] border-0">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-white text-base flex items-center gap-2">
                                <Tag class="w-4 h-4" />
                                分类信息
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Link :href="`/community/posts?category=${post.category.slug}`"
                                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#1f2937] transition-colors group">
                                <div class="text-2xl">{{ post.category.icon || '📝' }}</div>
                                <div>
                                    <div class="text-white font-medium group-hover:text-[#ff6e02] transition-colors">
                                        {{ post.category.name }}
                                    </div>
                                    <div class="text-xs text-[#999999]">
                                        浏览更多相关内容
                                    </div>
                                </div>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </WebLayout>
</template>