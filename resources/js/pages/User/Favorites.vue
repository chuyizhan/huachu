<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Head, Link } from '@inertiajs/vue3';
import { Bookmark, Eye, Heart, MessageSquare, ArrowLeft } from 'lucide-vue-next';

interface Creator {
    id: number;
    display_name: string;
    specialty: string;
    verification_status: string;
    rating?: number;
}

interface User {
    id: number;
    name: string;
    avatar?: string;
    creator_profile?: Creator;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
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
    images: string[];
    published_at: string;
    created_at: string;
    user: User;
    category: Category;
}

interface Props {
    favorites: {
        data: Post[];
        links: any;
        meta: any;
    };
}

defineProps<Props>();

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

// Function to unfavorite a post (optimistic update)
const unfavoritePost = async (postId: number) => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch(`/posts/${postId}/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            credentials: 'same-origin',
        });

        if (response.ok) {
            // Refresh the page to update the list
            window.location.reload();
        }
    } catch (error) {
        console.error('Error unfavoriting post:', error);
    }
};
</script>

<template>
    <WebLayout>
        <Head title="我的收藏" />

        <div class="min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Header -->
                <div class="flex items-center mb-8">
                    <Link href="/" class="mr-4">
                        <Button variant="ghost" size="sm" class="text-white hover:text-[#ff6e02]">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            返回首页
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                            <Bookmark class="h-8 w-8 text-[#ff6e02]" />
                            我的收藏
                        </h1>
                        <p class="text-[#999999]">管理和浏览你收藏的精彩内容</p>
                    </div>
                </div>

                <!-- Favorites List -->
                <div v-if="favorites.data.length === 0" class="text-center py-16">
                    <Bookmark class="h-16 w-16 text-[#666666] mx-auto mb-6" />
                    <h3 class="text-xl font-medium text-white mb-4">还没有收藏内容</h3>
                    <p class="text-[#999999] mb-8 max-w-md mx-auto">
                        发现有趣的帖子时，点击收藏按钮保存到这里，方便以后查看
                    </p>
                    <Link href="/">
                        <Button class="bg-[#ff6e02] hover:bg-[#e55a00] text-white">
                            去发现内容
                        </Button>
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        v-for="post in favorites.data"
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
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="unfavoritePost(post.id)"
                                    class="text-[#ff6e02] hover:text-red-400 hover:bg-red-900/20 p-1"
                                >
                                    <Bookmark class="h-4 w-4 fill-current" />
                                </Button>
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

                            <!-- Author Info -->
                            <div class="flex items-center gap-3 mb-4">
                                <Avatar class="h-8 w-8">
                                    <AvatarImage
                                        v-if="post.user.avatar"
                                        :src="post.user.avatar"
                                        :alt="post.user.name"
                                    />
                                    <AvatarFallback class="bg-[#ff6e02] text-white font-semibold text-xs">
                                        {{ getInitials(post.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white font-medium truncate">
                                        {{ post.user.creator_profile?.display_name || post.user.name }}
                                    </p>
                                    <p class="text-xs text-[#999999]">
                                        {{ formatDate(post.published_at || post.created_at) }}
                                    </p>
                                </div>
                            </div>

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
                            </div>

                            <!-- Tags -->
                            <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-1 mt-3">
                                <Badge
                                    v-for="tag in post.tags.slice(0, 3)"
                                    :key="tag"
                                    class="text-xs bg-[#1c1c1c] text-[#999999] border-[#4B5563]"
                                >
                                    #{{ tag }}
                                </Badge>
                                <span v-if="post.tags.length > 3" class="text-xs text-[#999999] self-center">
                                    +{{ post.tags.length - 3 }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination would go here -->
                <div v-if="favorites.data.length > 0" class="mt-12 flex justify-center">
                    <p class="text-[#999999] text-sm">
                        共收藏 {{ favorites.meta?.total || favorites.data.length }} 篇内容
                    </p>
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