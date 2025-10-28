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
import { Carousel, Slide, Pagination } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';
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
    UserMinus,
    Edit,
    Trash2,
    X
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

// Image modal state
const showImageModal = ref(false);
const currentImage = ref('');

// Check if user is authenticated
const isAuthenticated = computed(() => page.props.auth?.user);

// Check if current user is the author
const isAuthor = computed(() => {
    return isAuthenticated.value && page.props.auth?.user?.id === props.post.user.id;
});

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

// Delete post function
const deletePost = () => {
    if (confirm('确定要删除这篇帖子吗？此操作不可恢复。')) {
        router.delete(`/posts/${props.post.id}`, {
            onSuccess: () => {
                // Will be redirected by the backend
            },
            onError: (errors) => {
                alert('删除失败，请重试');
            }
        });
    }
};

// Image modal functions
const openImageModal = (imageUrl: string) => {
    currentImage.value = imageUrl;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    currentImage.value = '';
};
</script>

<template>
    <WebLayout>
        <div class="min-h-screen bg-[#1c1c1c]">
            <!-- Back Button -->
            <div class="px-6 pt-6 pb-4">
                <Link href="/" class="inline-flex items-center text-[#ff6e02] hover:text-[#e55a00] text-sm">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    返回首页
                </Link>
            </div>

            <div class="flex flex-col">
                <!-- Images/Video Swiper -->
                <div v-if="post.image_urls && post.image_urls.length > 0" class="mb-0">
                    <div v-if="post.image_urls.length === 1">
                        <img
                            :src="post.image_urls[0].url"
                            alt="Post image"
                            class="w-full h-[350px] object-contain bg-black cursor-pointer"
                            @click="openImageModal(post.image_urls[0].url)"
                        />
                    </div>
                    <div v-else class="relative">
                        <!-- Multiple images carousel -->
                        <Carousel :wrap-around="true">
                            <Slide v-for="image in post.image_urls" :key="image.id">
                                <div class="w-full h-[350px] bg-black">
                                    <img
                                        :src="image.url"
                                        alt="Post image"
                                        class="w-full h-full object-contain cursor-pointer"
                                        @click="openImageModal(image.url)"
                                    />
                                </div>
                            </Slide>
                            <template #addons>
                                <Pagination />
                            </template>
                        </Carousel>
                    </div>
                </div>

                <!-- Video Player -->
                <div v-if="post.video_urls && post.video_urls.length > 0 && canViewContent" class="mb-0 bg-black">
                    <video
                        :src="post.video_urls[0].url"
                        :poster="post.image_urls && post.image_urls.length > 0 ? post.image_urls[0].url : ''"
                        controls
                        controlslist="nodownload"
                        preload="metadata"
                        class="w-full max-h-[500px] object-contain"
                        style="outline: none;"
                    >
                        <source :src="post.video_urls[0].url" type="video/mp4" />
                        您的浏览器不支持视频播放
                    </video>
                </div>

                <!-- Title and Meta -->
                <div class="px-6 py-6">
                    <!-- Title in Orange -->
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <h1 class="text-xl font-medium text-[#ff6e02] flex-1">
                            {{ post.title }}
                        </h1>
                        <!-- Author Actions -->
                        <div v-if="isAuthor" class="flex items-center gap-2">
                            <Link
                                :href="`/posts/${post.id}/edit`"
                                class="text-[#999999] hover:text-[#ff6e02] transition-colors"
                            >
                                <Edit class="h-4 w-4" />
                            </Link>
                            <button
                                @click="deletePost"
                                class="text-red-400 hover:text-red-300 transition-colors"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="flex items-center gap-1 text-xs text-[#999999] mb-6">
                        <Calendar class="w-4 h-4" />
                        <span>{{ formatDate(post.published_at) }}</span>
                    </div>

                    <!-- Stats and User Info Row -->
                    <div class="flex items-center justify-between mb-0">
                        <!-- Left: Views, Likes, Favorites -->
                        <div class="flex items-center gap-5">
                            <span class="flex items-center gap-1 text-sm text-white">
                                <Eye class="w-4 h-4" />
                                {{ post.view_count }}
                            </span>
                            <button
                                @click="toggleLike"
                                :disabled="isLiking"
                                class="flex items-center gap-1 text-sm transition-colors"
                                :class="isLiked ? 'text-[#ff6e02]' : 'text-white'"
                            >
                                <ThumbsUp :class="['w-4 h-4', isLiked ? 'fill-current' : '']" />
                                {{ likeCount }}
                            </button>
                            <button
                                @click="toggleFavorite"
                                :disabled="isFavoriting"
                                class="flex items-center gap-1 text-sm transition-colors"
                                :class="isFavorited ? 'text-[#ff6e02]' : 'text-white'"
                            >
                                <Star :class="['w-4 h-4', isFavorited ? 'fill-current' : '']" />
                                {{ post.like_count }}
                            </button>
                        </div>

                        <!-- Right: User Avatar + Name + Follow Button -->
                        <div class="flex items-center gap-3">
                            <Link
                                v-if="post.user.creator_profile"
                                :href="`/creators/${post.user.creator_profile.id}`"
                                class="flex items-center gap-2"
                            >
                                <Avatar class="w-8 h-8">
                                    <AvatarFallback class="bg-[#374151] text-white text-xs">
                                        {{ getInitials(post.user.creator_profile.display_name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="text-sm text-white">{{ post.user.creator_profile.display_name }}</span>
                            </Link>
                            <div v-else class="flex items-center gap-2">
                                <Avatar class="w-8 h-8">
                                    <AvatarFallback class="bg-[#374151] text-white text-xs">
                                        {{ getInitials(post.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="text-sm text-white">{{ post.user.name }}</span>
                            </div>
                            <Button
                                v-if="post.user.creator_profile && !isAuthor"
                                size="sm"
                                @click="toggleFollow"
                                :disabled="isFollowing"
                                :class="isFollowingCreator
                                    ? 'bg-[#999999] text-white hover:bg-[#777777]'
                                    : 'bg-transparent border border-[#999999] text-[#999999] hover:bg-[#999999] hover:text-white'"
                                class="text-xs px-3 py-1 rounded-full transition-colors"
                            >
                                {{ isFollowingCreator ? '已关注' : '未关注' }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="px-6 py-6">
                    <!-- Section Title -->
                    <div class="flex items-center gap-2 mb-6">
                        <MessageSquare class="w-5 h-5 text-[#ff6e02]" />
                        <h2 class="text-xl font-medium text-[#ff6e02]">详情内容</h2>
                    </div>

                    <!-- Text Content - Only show if user can view -->
                    <div v-if="canViewContent" class="text-white text-sm leading-relaxed whitespace-pre-line mb-8">
                        {{ post.content }}
                    </div>

                    <!-- Locked Content Overlay -->
                    <div v-else-if="isLocked" class="mb-8">
                        <div class="bg-[#374151] border border-[#ff6e02] rounded-lg p-8 text-center">
                            <div class="text-6xl mb-4">🔒</div>
                            <h3 class="text-xl font-bold text-white mb-2">此内容需要付费解锁</h3>
                            <p class="text-[#999999] mb-6 text-sm">
                                作者设置此内容需要 <span class="text-[#ff6e02] font-bold">{{ post.price }}</span> 金币才能查看
                            </p>

                            <div v-if="post.free_after" class="mb-4 text-xs text-[#999999]">
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
                                    class="w-full bg-[#ff6e02] hover:bg-[#e55a00] text-white h-12 rounded-lg"
                                >
                                    {{ isPurchasing ? '购买中...' : `立即购买 ( 金币：${post.price} )` }}
                                </Button>
                                <div v-else class="text-red-400 text-sm">
                                    金币不足，需要 {{ post.price - userCreditsRef }} 更多金币
                                </div>
                            </div>

                            <div v-else>
                                <Button
                                    as="a"
                                    href="/login"
                                    class="w-full bg-[#ff6e02] hover:bg-[#e55a00] text-white h-12 rounded-lg"
                                >
                                    登录后购买
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Circular Action Buttons (Like & Favorite) -->
                <div class="flex items-center justify-center gap-20 py-12">
                    <button
                        @click="toggleLike"
                        :disabled="isLiking"
                        :class="[
                            'flex flex-col items-center justify-center w-32 h-32 rounded-full transition-colors',
                            isLiked ? 'bg-[#ff6e02]' : 'bg-[#374151]'
                        ]"
                    >
                        <ThumbsUp :class="['w-5 h-5 mb-1', isLiked ? 'text-white fill-current' : 'text-white']" />
                        <span class="text-xs text-white">{{ isLiked ? '已点赞' : '点赞' }}</span>
                    </button>
                    <button
                        @click="toggleFavorite"
                        :disabled="isFavoriting"
                        :class="[
                            'flex flex-col items-center justify-center w-32 h-32 rounded-full transition-colors',
                            isFavorited ? 'bg-[#ff6e02]' : 'bg-[#374151]'
                        ]"
                    >
                        <Star :class="['w-5 h-5 mb-1', isFavorited ? 'text-white fill-current' : 'text-white']" />
                        <span class="text-xs text-white">{{ isFavorited ? '已收藏' : '收藏' }}</span>
                    </button>
                </div>

                <!-- Comments Section -->
                <div class="px-6 py-6">
                    <Comments
                        :comments="post.comments || []"
                        :commentable-type="'App\\Models\\Post'"
                        :commentable-id="post.id"
                    />
                </div>
            </div>

            <!-- Image Modal -->
            <div
                v-if="showImageModal"
                @click="closeImageModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
            >
                <div class="relative max-w-4xl max-h-[90vh]">
                    <button
                        @click="closeImageModal"
                        class="absolute -top-10 right-0 text-white hover:text-[#ff6e02] transition-colors"
                    >
                        <X class="w-8 h-8" />
                    </button>
                    <img
                        :src="currentImage"
                        alt="Full size image"
                        class="max-w-full max-h-[90vh] object-contain"
                        @click.stop
                    />
                </div>
            </div>
        </div>
    </WebLayout>
</template>

<style scoped>
/* Carousel dark theme styling */
:deep(.carousel__pagination-button) {
    background-color: rgba(255, 255, 255, 0.5);
}

:deep(.carousel__pagination-button--active) {
    background-color: #ff6e02;
}
</style>