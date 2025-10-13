<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import Comments from '@/components/Comments.vue';
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
    ThumbsUp,
    UserPlus,
    UserMinus
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

interface MediaImage {
    id: number;
    url: string;
    thumb: string;
    medium: string;
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
    price?: number;
    free_after?: string;
    view_count: number;
    like_count: number;
    comment_count: number;
    share_count: number;
    tags: string[];
    published_at: string;
    created_at: string;
    user: User;
    category: Category;
    image_urls?: MediaImage[];
}

interface Props {
    post: Post;
    relatedPosts: Post[];
    userInteractions: {
        liked: boolean;
        favorited: boolean;
        following_creator: boolean;
    };
    canViewContent: boolean;
    isLocked: boolean;
    isPurchased: boolean;
    userCredits: number;
    commentsCount: number;
}

const props = defineProps<Props>();
console.log(props.post);
const page = usePage();

// Reactive state for like functionality
const isLiked = ref(props.userInteractions.liked);
const likeCount = ref(props.post.like_count);
const isLiking = ref(false);

// Reactive state for favorite functionality
const isFavorited = ref(props.userInteractions.favorited);
const isFavoriting = ref(false);

// Reactive state for follow functionality
const isFollowingCreator = ref(props.userInteractions.following_creator);
const followerCount = ref(props.post.user.creator_profile?.follower_count || 0);
const isFollowing = ref(false);

// Reactive state for purchase
const isPurchasing = ref(false);
const canView = ref(props.canViewContent);
const userCreditsRef = ref(props.userCredits);

// Check if user is authenticated
const isAuthenticated = computed(() => page.props.auth?.user);

// Toggle like function using Axios (handles CSRF automatically)
const toggleLike = async () => {
    if (!isAuthenticated.value) {
        router.visit('/login');
        return;
    }

    if (isLiking.value) return;

    isLiking.value = true;

    try {
        const response = await axios.post(`/posts/${props.post.id}/like`);

        if (response.data.success) {
            isLiked.value = response.data.liked;
            likeCount.value = response.data.like_count;
        } else {
            console.error('Failed to toggle like:', response.data.message);
            alert(response.data.message || '操作失败，请重试');
        }
    } catch (error: any) {
        console.error('Error toggling like:', error);
        alert(error.response?.data?.message || '网络错误，请重试');
    } finally {
        isLiking.value = false;
    }
};

// Toggle favorite function using Axios
const toggleFavorite = async () => {
    if (!isAuthenticated.value) {
        router.visit('/login');
        return;
    }

    if (isFavoriting.value) return;

    isFavoriting.value = true;

    try {
        const response = await axios.post(`/posts/${props.post.id}/favorite`);

        if (response.data.success) {
            isFavorited.value = response.data.favorited;
        } else {
            console.error('Failed to toggle favorite:', response.data.message);
            alert(response.data.message || '收藏操作失败，请重试');
        }
    } catch (error: any) {
        console.error('Error toggling favorite:', error);
        alert(error.response?.data?.message || '网络错误，请重试');
    } finally {
        isFavoriting.value = false;
    }
};

// Toggle follow function using Axios
const toggleFollow = async () => {
    if (!isAuthenticated.value) {
        router.visit('/login');
        return;
    }

    if (!props.post.user.creator_profile) {
        alert('该用户没有创作者档案');
        return;
    }

    if (isFollowing.value) return;

    isFollowing.value = true;

    try {
        const response = await axios.post(`/creators/${props.post.user.creator_profile.id}/follow`);

        if (response.data.success) {
            isFollowingCreator.value = response.data.following;
            followerCount.value = response.data.followers_count;
        } else {
            console.error('Failed to toggle follow:', response.data.message);
            alert(response.data.message || '关注操作失败，请重试');
        }
    } catch (error: any) {
        console.error('Error toggling follow:', error);
        alert(error.response?.data?.message || '网络错误，请重试');
    } finally {
        isFollowing.value = false;
    }
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

// Purchase post function using Axios
const purchasePost = async () => {
    if (!isAuthenticated.value) {
        router.visit('/login');
        return;
    }

    if (isPurchasing.value) return;

    if (confirm(`确定要花费 ${props.post.price} 积分购买此内容吗？`)) {
        isPurchasing.value = true;

        try {
            const response = await axios.post(`/posts/${props.post.id}/purchase`);

            if (response.data.success) {
                canView.value = true;
                userCreditsRef.value = response.data.remaining_credits;
                alert(response.data.message);
                // Reload page to show full content
                router.reload();
            } else {
                alert(response.data.message || '购买失败');
            }
        } catch (error: any) {
            console.error('Purchase error:', error);
            alert(error.response?.data?.message || '网络错误，请重试');
        } finally {
            isPurchasing.value = false;
        }
    }
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
                            <Badge v-if="post.price && post.price > 0" class="bg-green-600 text-white text-xs">
                                💰 {{ post.price }} 积分
                            </Badge>
                            <Badge v-if="isPurchased" class="bg-blue-600 text-white text-xs">
                                ✓ 已购买
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
                                <Link
                                    v-if="post.user.creator_profile"
                                    :href="`/creators/${post.user.creator_profile.id}`"
                                    class="flex items-center gap-3 hover:opacity-80 transition-opacity"
                                >
                                    <Avatar class="w-10 h-10">
                                        <AvatarFallback class="bg-[#1f2937] text-white text-sm">
                                            {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-white font-medium hover:text-[#ff6e02] transition-colors">
                                                {{ post.user.creator_profile.display_name }}
                                            </span>
                                            <ChefHat v-if="post.user.creator_profile.verification_status === 'verified'"
                                                    class="w-4 h-4 text-[#ff6e02]" />
                                        </div>
                                        <div class="text-xs text-[#999999]">
                                            {{ post.user.creator_profile.specialty }}
                                        </div>
                                    </div>
                                </Link>
                                <div v-else class="flex items-center gap-3">
                                    <Avatar class="w-10 h-10">
                                        <AvatarFallback class="bg-[#1f2937] text-white text-sm">
                                            {{ getInitials(post.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-white font-medium">
                                                {{ post.user.name }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-[#999999]">
                                            美食爱好者
                                        </div>
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
                            <!-- Images Gallery -->
                            <div v-if="post.image_urls && post.image_urls.length > 0" class="mb-6">
                                <div :class="post.image_urls.length === 1 ? 'grid grid-cols-1' : 'grid grid-cols-2 gap-3'">
                                    <div
                                        v-for="image in post.image_urls"
                                        :key="image.id"
                                       
                                        class="group relative rounded-lg overflow-hidden border border-[#4B5563] hover:border-[#ff6e02] transition-colors"
                                    >
                                        <img
                                            :src="image.medium"
                                            alt="Post image"
                                            class="w-full h-auto object-cover group-hover:opacity-90 transition-opacity"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Text Content - Only show if user can view -->
                            <div v-if="canViewContent" class="prose prose-invert max-w-none">
                                <div class="text-white leading-relaxed whitespace-pre-line">
                                    {{ post.content }}
                                </div>
                            </div>

                            <!-- Locked Content Overlay - Show instead of content when locked -->
                            <div v-else-if="isLocked" class="prose prose-invert max-w-none">
                                <div class="bg-[#1c1c1c] border border-[#ff6e02] rounded-lg p-8 text-center">
                                    <div class="text-6xl mb-4">🔒</div>
                                    <h3 class="text-2xl font-bold text-white mb-2">此内容需要付费解锁</h3>
                                    <p class="text-[#999999] mb-6">
                                        作者设置此内容需要 <span class="text-[#ff6e02] font-bold text-xl">{{ post.price }}</span> 金币才能查看
                                    </p>

                                    <div v-if="post.free_after" class="mb-4 text-sm text-[#999999]">
                                        ⏰ 将在 {{ formatDate(post.free_after) }} 后免费开放
                                    </div>

                                    <div v-if="isAuthenticated" class="space-y-4">
                                        <div class="text-sm text-[#999999]">
                                            你的金币余额: <span class="text-white font-semibold">{{ userCreditsRef }}</span>
                                        </div>

                                        <Button
                                            v-if="userCreditsRef >= post.price"
                                            @click="purchasePost"
                                            :disabled="isPurchasing"
                                            class="bg-[#ff6e02] hover:bg-[#e55a00] text-white px-8 py-3 text-lg"
                                        >
                                            {{ isPurchasing ? '购买中...' : `花费 ${post.price} 金币解锁内容` }}
                                        </Button>
                                        <div v-else class="text-red-400">
                                            金币不足，需要 {{ post.price - userCreditsRef }} 更多金币
                                        </div>
                                    </div>

                                    <div v-else>
                                        <Button
                                            as="a"
                                            href="/login"
                                            class="bg-[#ff6e02] hover:bg-[#e55a00] text-white px-8 py-3 text-lg"
                                        >
                                            登录后购买
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Comments Section -->
                    <div class="bg-[#374151] rounded-lg p-6 mb-6">
                        <Comments
                            :comments="post.comments || []"
                            :commentable-type="'App\\Models\\Post'"
                            :commentable-id="post.id"
                        />
                    </div>

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
                                            <Link
                                                v-if="relatedPost.user.creator_profile"
                                                :href="`/creators/${relatedPost.user.creator_profile.id}`"
                                                class="hover:text-[#ff6e02] transition-colors"
                                                @click.stop
                                            >
                                                {{ relatedPost.user.creator_profile.display_name }}
                                            </Link>
                                            <span v-else>{{ relatedPost.user.name }}</span>
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
                                <Link
                                    v-if="post.user.creator_profile"
                                    :href="`/creators/${post.user.creator_profile.id}`"
                                    class="block hover:opacity-80 transition-opacity"
                                >
                                    <Avatar class="w-16 h-16 mx-auto mb-3">
                                        <AvatarFallback class="bg-[#1f2937] text-white text-lg">
                                            {{ getInitials(post.user.creator_profile.display_name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="mb-2">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            <span class="text-white font-medium hover:text-[#ff6e02] transition-colors">
                                                {{ post.user.creator_profile.display_name }}
                                            </span>
                                            <ChefHat v-if="post.user.creator_profile.verification_status === 'verified'"
                                                    class="w-4 h-4 text-[#ff6e02]" />
                                        </div>
                                        <div class="text-xs text-[#999999] mb-2">
                                            {{ post.user.creator_profile.specialty }}
                                        </div>
                                        <div class="flex items-center justify-center gap-4 text-xs text-[#999999] mb-3">
                                            <span v-if="post.user.creator_profile.rating" class="flex items-center gap-1">
                                                <Star class="w-3 h-3 text-yellow-400" />
                                                {{ post.user.creator_profile.rating }}
                                            </span>
                                            <span v-if="followerCount > 0">
                                                {{ followerCount }} 粉丝
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                                <div v-else class="mb-2">
                                    <Avatar class="w-16 h-16 mx-auto mb-3">
                                        <AvatarFallback class="bg-[#1f2937] text-white text-lg">
                                            {{ getInitials(post.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        <span class="text-white font-medium">
                                            {{ post.user.name }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-[#999999] mb-2">
                                        美食爱好者
                                    </div>
                                </div>
                                <Button
                                    v-if="post.user.creator_profile"
                                    size="sm"
                                    :class="isFollowingCreator
                                        ? 'bg-transparent border-[#ff6e02] text-[#ff6e02] hover:bg-[#ff6e02] hover:text-white'
                                        : 'bg-[#ff6e02] text-white hover:bg-[#e55a00]'"
                                    class="text-xs w-full transition-colors"
                                    @click="toggleFollow"
                                    :disabled="isFollowing"
                                >
                                    <span v-if="isFollowing" class="flex items-center justify-center gap-1">
                                        处理中...
                                    </span>
                                    <span v-else class="flex items-center justify-center gap-1">
                                        <UserPlus v-if="!isFollowingCreator" class="w-3 h-3" />
                                        <UserMinus v-else class="w-3 h-3" />
                                        {{ isFollowingCreator ? '已关注' : '关注' }}
                                    </span>
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