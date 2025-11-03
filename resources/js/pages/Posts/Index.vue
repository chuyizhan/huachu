<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusCircle, Eye, Heart, MessageSquare, Calendar, Edit, Trash2 } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    type: 'discussion' | 'tutorial' | 'showcase' | 'question';
    status: 'draft' | 'published' | 'archived';
    review_status: 'pending' | 'approved' | 'rejected';
    review_notes?: string;
    is_premium: boolean;
    is_featured?: boolean;
    has_video?: boolean;
    view_count: number;
    like_count: number;
    comment_count: number;
    published_at?: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
        creator_profile?: {
            id: number;
            display_name: string;
            specialty: string;
            verification_status?: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
        icon?: string;
        icon_image?: string | null;
    };
    first_image?: {
        url: string;
        thumb: string;
    } | null;
    post_images?: Array<{
        url: string;
        thumb: string;
    }>;
}

interface Stats {
    total: number;
    published: number;
    drafts: number;
    premium: number;
    pending_review: number;
    approved: number;
    rejected: number;
}

interface Props {
    posts: {
        data: Post[];
        links: any;
        meta: any;
    };
    stats: Stats;
}

const props = defineProps<Props>();

// Helper function to get post images
const getPostImages = (post: Post) => {
    if (post.post_images && post.post_images.length > 0) {
        return post.post_images;
    }
    if (post.first_image) {
        return [post.first_image];
    }
    return [];
};

const postTypeLabels = {
    discussion: '讨论',
    tutorial: '教程',
    showcase: '展示',
    question: '问题'
};

const statusLabels = {
    draft: '草稿',
    published: '已发布',
    archived: '已归档'
};

const reviewStatusLabels = {
    pending: '审核中',
    approved: '已批准',
    rejected: '已拒绝'
};

const reviewStatusColors = {
    pending: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500'
};

function deletePost(postId: number, postTitle: string) {
    if (confirm(`确认删除帖子 "${postTitle}"？\n\n此操作无法撤销。`)) {
        router.delete(`/posts/${postId}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by Inertia
            },
            onError: (errors) => {
                console.error('Failed to delete post:', errors);
                alert('删除失败，请重试');
            }
        });
    }
}
</script>

<template>
    <WebLayout>
        <Head title="我的帖子" />

        <div class="min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-2">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">我的帖子</h1>
                        <p class="text-[#999999]">管理你的所有帖子内容</p>
                    </div>
                    <Link href="/posts/create">
                        <Button class="bg-[#ff6e02] hover:bg-[#e55a00] text-white">
                            <PlusCircle class="h-4 w-4 mr-2" />
                            创建新帖子
                        </Button>
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-4 md:grid-cols-4 gap-2 md:gap-6 mb-8">
                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-2 md:p-6">
                            <div class="flex flex-col md:flex-row items-center md:items-center">
                                <div class="p-1 md:p-2 bg-[#ff6e02]/20 rounded-lg mb-1 md:mb-0">
                                    <Edit class="h-3 w-3 md:h-5 md:w-5 text-[#ff6e02]" />
                                </div>
                                <div class="md:ml-4 text-center md:text-left">
                                    <p class="text-xs md:text-sm text-[#999999]">总帖子</p>
                                    <p class="text-sm md:text-2xl font-bold text-white">{{ stats.total }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-2 md:p-6">
                            <div class="flex flex-col md:flex-row items-center md:items-center">
                                <div class="p-1 md:p-2 bg-green-500/20 rounded-lg mb-1 md:mb-0">
                                    <Eye class="h-3 w-3 md:h-5 md:w-5 text-green-500" />
                                </div>
                                <div class="md:ml-4 text-center md:text-left">
                                    <p class="text-xs md:text-sm text-[#999999]">已发布</p>
                                    <p class="text-sm md:text-2xl font-bold text-white">{{ stats.published }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-2 md:p-6">
                            <div class="flex flex-col md:flex-row items-center md:items-center">
                                <div class="p-1 md:p-2 bg-yellow-500/20 rounded-lg mb-1 md:mb-0">
                                    <Edit class="h-3 w-3 md:h-5 md:w-5 text-yellow-500" />
                                </div>
                                <div class="md:ml-4 text-center md:text-left">
                                    <p class="text-xs md:text-sm text-[#999999]">草稿</p>
                                    <p class="text-sm md:text-2xl font-bold text-white">{{ stats.drafts }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-2 md:p-6">
                            <div class="flex flex-col md:flex-row items-center md:items-center">
                                <div class="p-1 md:p-2 bg-purple-500/20 rounded-lg mb-1 md:mb-0">
                                    <Heart class="h-3 w-3 md:h-5 md:w-5 text-purple-500" />
                                </div>
                                <div class="md:ml-4 text-center md:text-left">
                                    <p class="text-xs md:text-sm text-[#999999]">高级内容</p>
                                    <p class="text-sm md:text-2xl font-bold text-white">{{ stats.premium }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Posts List -->
                <Card class="bg-[#374151] border-[#4B5563]">
                    <CardHeader>
                        <CardTitle class="text-white">帖子列表</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="posts.data.length === 0" class="text-center py-12">
                            <Edit class="h-12 w-12 text-[#999999] mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-white mb-2">还没有帖子</h3>
                            <p class="text-[#999999] mb-6">创建你的第一个帖子来分享想法</p>
                            <Link href="/posts/create">
                                <Button class="bg-[#ff6e02] hover:bg-[#e55a00] text-white">
                                    <PlusCircle class="h-4 w-4 mr-2" />
                                    创建帖子
                                </Button>
                            </Link>
                        </div>

                        <div v-else class="divide-y divide-[#4B5563]">
                            <div
                                v-for="post in posts.data"
                                :key="post.id"
                                class="relative py-4 first:pt-0 hover:bg-[#1f2937]/30 transition-colors px-4"
                            >
                                <!-- Status Badges and Actions -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <Badge class="bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30 text-xs">
                                            {{ postTypeLabels[post.type] }}
                                        </Badge>
                                        <Badge
                                            :class="{
                                                'bg-green-500/20 text-green-500 border-green-500/30': post.status === 'published',
                                                'bg-yellow-500/20 text-yellow-500 border-yellow-500/30': post.status === 'draft',
                                                'bg-gray-500/20 text-gray-500 border-gray-500/30': post.status === 'archived'
                                            }"
                                            class="text-xs"
                                        >
                                            {{ statusLabels[post.status] }}
                                        </Badge>
                                        <Badge
                                            v-if="post.status === 'published'"
                                            :class="reviewStatusColors[post.review_status]"
                                            class="text-xs text-white"
                                        >
                                            {{ reviewStatusLabels[post.review_status] }}
                                        </Badge>
                                        <Badge
                                            v-if="post.is_premium"
                                            class="bg-purple-500/20 text-purple-500 border-purple-500/30 text-xs"
                                        >
                                            VIP
                                        </Badge>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: post.category.color }"></div>
                                            <span class="text-xs text-[#999999]">{{ post.category.name }}</span>
                                        </div>
                                    </div>

                                    <!-- Management Actions -->
                                    <div class="flex items-center gap-1">
                                        <Link :href="`/posts/${post.id}/edit`">
                                            <Button variant="ghost" size="sm" class="text-[#999999] hover:text-white hover:bg-[#4B5563]">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-400 hover:text-red-300 hover:bg-red-900/20"
                                            @click="deletePost(post.id, post.title)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Review Notes (if rejected) -->
                                <div
                                    v-if="post.review_status === 'rejected' && post.review_notes"
                                    class="mb-3 p-3 bg-red-500/10 border border-red-500/30 rounded-lg"
                                >
                                    <p class="text-sm text-red-400">
                                        <strong>拒绝原因:</strong> {{ post.review_notes }}
                                    </p>
                                </div>

                                <!-- Post Content -->
                                <Link :href="post.status === 'draft' ? `/posts/${post.id}/edit` : `/posts/${post.slug}`" class="block">
                                    <!-- Title -->
                                    <h3 class="text-lg font-semibold text-white hover:text-[#ff6e02] transition-colors mb-2 line-clamp-2">
                                        {{ post.title }}
                                    </h3>

                                    <!-- Excerpt -->
                                    <p v-if="post.excerpt" class="text-sm text-[#999999] mb-3 line-clamp-2">
                                        {{ post.excerpt }}
                                    </p>

                                    <!-- Post Images (First 3) -->
                                    <div v-if="getPostImages(post).length > 0" class="mb-3">
                                        <!-- Single Image -->
                                        <div v-if="getPostImages(post).length === 1" class="max-w-lg">
                                            <div class="relative overflow-hidden rounded-lg border border-[#4B5563]" style="aspect-ratio: 16/9;">
                                                <img
                                                    :src="getPostImages(post)[0].thumb || getPostImages(post)[0].url"
                                                    class="w-full h-full object-cover"
                                                    :alt="`${post.title} - Image 1`"
                                                />
                                            </div>
                                        </div>

                                        <!-- Two Images -->
                                        <div v-else-if="getPostImages(post).length === 2" class="grid grid-cols-2 gap-2 max-w-xl">
                                            <div
                                                v-for="(image, index) in getPostImages(post)"
                                                :key="index"
                                                class="relative overflow-hidden rounded-lg border border-[#4B5563]"
                                                style="aspect-ratio: 1/1;"
                                            >
                                                <img
                                                    :src="image.thumb || image.url"
                                                    class="w-full h-full object-cover"
                                                    :alt="`${post.title} - Image ${index + 1}`"
                                                />
                                            </div>
                                        </div>

                                        <!-- Three or More Images -->
                                        <div v-else class="grid grid-cols-3 gap-2 max-w-2xl">
                                            <div
                                                v-for="(image, index) in getPostImages(post).slice(0, 3)"
                                                :key="index"
                                                class="relative overflow-hidden rounded-lg border border-[#4B5563]"
                                                style="aspect-ratio: 1/1;"
                                            >
                                                <img
                                                    :src="image.thumb || image.url"
                                                    class="w-full h-full object-cover"
                                                    :alt="`${post.title} - Image ${index + 1}`"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </Link>

                                <!-- Stats -->
                                <div class="flex items-center gap-6 text-sm text-[#999999] mt-3">
                                    <div class="flex items-center gap-1.5">
                                        <Eye class="h-4 w-4" />
                                        <span>{{ post.view_count }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Heart class="h-4 w-4" />
                                        <span>{{ post.like_count }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <MessageSquare class="h-4 w-4" />
                                        <span>{{ post.comment_count }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 ml-auto">
                                        <Calendar class="h-4 w-4" />
                                        <span>{{ new Date(post.published_at || post.created_at).toLocaleDateString('zh-CN') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination would go here -->
                    </CardContent>
                </Card>
            </div>
        </div>
    </WebLayout>
</template>