<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import { PlusCircle, Eye, Heart, MessageSquare, Calendar, Edit, Trash2 } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    type: 'discussion' | 'tutorial' | 'showcase' | 'question';
    status: 'draft' | 'published' | 'archived';
    is_premium: boolean;
    view_count: number;
    like_count: number;
    comment_count: number;
    published_at?: string;
    created_at: string;
    category: {
        id: number;
        name: string;
        color?: string;
    };
}

interface Stats {
    total: number;
    published: number;
    drafts: number;
    premium: number;
}

interface Props {
    posts: {
        data: Post[];
        links: any;
        meta: any;
    };
    stats: Stats;
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

const statusLabels = {
    draft: '草稿',
    published: '已发布',
    archived: '已归档'
};

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<template>
    <WebLayout>
        <Head title="我的帖子" />

        <div class="min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-4">
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-[#ff6e02]/20 rounded-lg">
                                    <Edit class="h-5 w-5 text-[#ff6e02]" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-[#999999]">总帖子</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-500/20 rounded-lg">
                                    <Eye class="h-5 w-5 text-green-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-[#999999]">已发布</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.published }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-500/20 rounded-lg">
                                    <Edit class="h-5 w-5 text-yellow-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-[#999999]">草稿</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.drafts }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-500/20 rounded-lg">
                                    <Heart class="h-5 w-5 text-purple-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-[#999999]">高级内容</p>
                                    <p class="text-2xl font-bold text-white">{{ stats.premium }}</p>
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

                        <div v-else class="space-y-4">
                            <div
                                v-for="post in posts.data"
                                :key="post.id"
                                class="border border-[#4B5563] rounded-lg p-6 hover:border-[#6B7280] transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Post Header -->
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="text-lg">{{ postTypeIcons[post.type] }}</span>
                                            <Badge class="bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30">
                                                {{ postTypeLabels[post.type] }}
                                            </Badge>
                                            <Badge
                                                :class="{
                                                    'bg-green-500/20 text-green-500 border-green-500/30': post.status === 'published',
                                                    'bg-yellow-500/20 text-yellow-500 border-yellow-500/30': post.status === 'draft',
                                                    'bg-gray-500/20 text-gray-500 border-gray-500/30': post.status === 'archived'
                                                }"
                                            >
                                                {{ statusLabels[post.status] }}
                                            </Badge>
                                            <Badge
                                                v-if="post.is_premium"
                                                class="bg-purple-500/20 text-purple-500 border-purple-500/30"
                                            >
                                                VIP
                                            </Badge>
                                            <div class="w-3 h-3 rounded-full bg-[#ff6e02]"></div>
                                            <span class="text-sm text-[#999999]">{{ post.category.name }}</span>
                                        </div>

                                        <!-- Post Title and Content -->
                                        <Link :href="`/posts/${post.slug}`">
                                            <h3 class="text-xl font-semibold text-white hover:text-[#ff6e02] transition-colors mb-2">
                                                {{ post.title }}
                                            </h3>
                                        </Link>

                                        <p v-if="post.excerpt" class="text-[#999999] mb-4 line-clamp-2">
                                            {{ post.excerpt }}
                                        </p>

                                        <!-- Post Stats -->
                                        <div class="flex items-center gap-6 text-sm text-[#999999]">
                                            <div class="flex items-center gap-1">
                                                <Eye class="h-4 w-4" />
                                                {{ post.view_count }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <Heart class="h-4 w-4" />
                                                {{ post.like_count }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <MessageSquare class="h-4 w-4" />
                                                {{ post.comment_count }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <Calendar class="h-4 w-4" />
                                                {{ formatDate(post.published_at || post.created_at) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 ml-4">
                                        <Link :href="`/posts/${post.id}/edit`">
                                            <Button variant="ghost" size="sm" class="text-[#999999] hover:text-white hover:bg-[#4B5563]">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="sm" class="text-red-400 hover:text-red-300 hover:bg-red-900/20">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
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