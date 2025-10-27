<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CheckCircle, XCircle, RotateCcw, ArrowLeft, User, Calendar, Tag, Image as ImageIcon, Video } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    slug: string;
    content?: string;
    excerpt?: string;
    user: {
        id: number;
        name: string;
        email: string;
        creator_profile?: {
            id: number;
            display_name: string;
        };
    };
    category: {
        id: number;
        name: string;
    };
    type: string;
    tags?: string[];
    review_status: 'pending' | 'approved' | 'rejected';
    reviewed_by?: number;
    reviewed_at?: string;
    review_notes?: string;
    reviewer?: {
        id: number;
        name: string;
    };
    created_at: string;
    published_at?: string;
    image_urls?: Array<{
        id: number;
        url: string;
        thumb: string;
    }>;
    video_urls?: Array<{
        id: number;
        url: string;
        name: string;
        size: number;
    }>;
}

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const rejectNotes = ref('');
const approveNotes = ref('');

function goBack() {
    router.get(route('admin.post-reviews.index'));
}

function approvePost() {
    if (confirm('确认批准这个帖子？')) {
        router.post(route('admin.post-reviews.approve', props.post.id), {
            notes: approveNotes.value || null,
        }, {
            onSuccess: () => goBack(),
        });
    }
}

function rejectPost() {
    if (!rejectNotes.value.trim()) {
        alert('请输入拒绝原因');
        return;
    }

    if (confirm('确认拒绝这个帖子？')) {
        router.post(route('admin.post-reviews.reject', props.post.id), {
            notes: rejectNotes.value,
        }, {
            onSuccess: () => goBack(),
        });
    }
}

function resetReview() {
    if (confirm('确认重置审核状态？')) {
        router.post(route('admin.post-reviews.reset', props.post.id), {}, {
            preserveScroll: true,
        });
    }
}

function getStatusColor(status: string) {
    switch (status) {
        case 'pending':
            return 'bg-yellow-500';
        case 'approved':
            return 'bg-green-500';
        case 'rejected':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
}

function formatDate(date?: string) {
    if (!date) return '-';
    return new Date(date).toLocaleString('zh-CN');
}

function formatFileSize(bytes: number) {
    const mb = bytes / (1024 * 1024);
    return mb.toFixed(2) + ' MB';
}
</script>

<template>
    <Head :title="`审核: ${post.title}`" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="goBack"
                        class="border-[#2a2a2a] text-white hover:bg-[#2a2a2a]"
                    >
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        返回列表
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-white">审核帖子</h1>
                        <p class="text-[#999999] mt-1">{{ post.title }}</p>
                    </div>
                </div>

                <Badge :class="getStatusColor(post.review_status)" class="text-base px-4 py-2">
                    {{ post.review_status === 'pending' ? '待审核' :
                       post.review_status === 'approved' ? '已批准' : '已拒绝' }}
                </Badge>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Post Info -->
                    <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">帖子内容</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-white">标题</Label>
                                <p class="text-white mt-2">{{ post.title }}</p>
                            </div>

                            <div v-if="post.excerpt">
                                <Label class="text-white">摘要</Label>
                                <p class="text-[#999999] mt-2">{{ post.excerpt }}</p>
                            </div>

                            <div v-if="post.content">
                                <Label class="text-white">内容</Label>
                                <div class="text-[#999999] mt-2 whitespace-pre-wrap">{{ post.content }}</div>
                            </div>

                            <div v-if="post.tags && post.tags.length > 0">
                                <Label class="text-white">标签</Label>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <Badge
                                        v-for="tag in post.tags"
                                        :key="tag"
                                        variant="outline"
                                        class="border-[#2a2a2a] text-white"
                                    >
                                        {{ tag }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Images -->
                    <Card v-if="post.image_urls && post.image_urls.length > 0" class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white flex items-center gap-2">
                                <ImageIcon class="h-5 w-5" />
                                图片 ({{ post.image_urls.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <img
                                    v-for="image in post.image_urls"
                                    :key="image.id"
                                    :src="image.url"
                                    :alt="`Image ${image.id}`"
                                    class="w-full h-48 object-cover rounded-lg"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Videos -->
                    <Card v-if="post.video_urls && post.video_urls.length > 0" class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white flex items-center gap-2">
                                <Video class="h-5 w-5" />
                                视频 ({{ post.video_urls.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-for="video in post.video_urls"
                                :key="video.id"
                            >
                                <video
                                    :src="video.url"
                                    controls
                                    class="w-full rounded-lg"
                                ></video>
                                <p class="text-xs text-[#999999] mt-2">
                                    {{ video.name }} ({{ formatFileSize(video.size) }})
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Review Notes (if exists) -->
                    <Card v-if="post.review_notes" class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">审核备注</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-[#999999]">{{ post.review_notes }}</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Author Info -->
                    <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">作者信息</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4 text-[#999999]" />
                                <div>
                                    <p class="text-white">{{ post.user.name }}</p>
                                    <p class="text-xs text-[#999999]">{{ post.user.email }}</p>
                                </div>
                            </div>

                            <div v-if="post.user.creator_profile" class="pt-2 border-t border-[#2a2a2a]">
                                <p class="text-xs text-[#999999]">创作者</p>
                                <p class="text-white">{{ post.user.creator_profile.display_name }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Post Meta -->
                    <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">帖子信息</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-[#999999]">分类</Label>
                                <Badge variant="outline" class="mt-1 border-[#2a2a2a] text-white">
                                    {{ post.category.name }}
                                </Badge>
                            </div>

                            <div>
                                <Label class="text-[#999999]">类型</Label>
                                <p class="text-white mt-1">{{ post.type }}</p>
                            </div>

                            <div>
                                <Label class="text-[#999999]">创建时间</Label>
                                <p class="text-white mt-1 text-sm">{{ formatDate(post.created_at) }}</p>
                            </div>

                            <div v-if="post.published_at">
                                <Label class="text-[#999999]">发布时间</Label>
                                <p class="text-white mt-1 text-sm">{{ formatDate(post.published_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Review Info -->
                    <Card v-if="post.reviewer" class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">审核信息</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-[#999999]">审核员</Label>
                                <p class="text-white mt-1">{{ post.reviewer.name }}</p>
                            </div>

                            <div>
                                <Label class="text-[#999999]">审核时间</Label>
                                <p class="text-white mt-1 text-sm">{{ formatDate(post.reviewed_at) }}</p>
                            </div>

                            <Button
                                size="sm"
                                variant="outline"
                                @click="resetReview"
                                class="w-full border-[#2a2a2a] text-white hover:bg-[#2a2a2a]"
                            >
                                <RotateCcw class="h-4 w-4 mr-2" />
                                重置审核
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Review Actions -->
                    <Card v-if="post.review_status === 'pending'" class="bg-[#1c1c1c] border-[#2a2a2a]">
                        <CardHeader>
                            <CardTitle class="text-white">审核操作</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Approve Section -->
                            <div>
                                <Label class="text-white">批准备注 (可选)</Label>
                                <Textarea
                                    v-model="approveNotes"
                                    placeholder="添加批准备注..."
                                    class="mt-2 bg-[#2a2a2a] border-[#3a3a3a] text-white"
                                />
                                <Button
                                    @click="approvePost"
                                    class="w-full mt-2 bg-green-600 hover:bg-green-700"
                                >
                                    <CheckCircle class="h-4 w-4 mr-2" />
                                    批准帖子
                                </Button>
                            </div>

                            <div class="border-t border-[#2a2a2a] pt-4">
                                <Label class="text-white">拒绝原因 *</Label>
                                <Textarea
                                    v-model="rejectNotes"
                                    placeholder="请输入拒绝原因..."
                                    class="mt-2 bg-[#2a2a2a] border-[#3a3a3a] text-white"
                                    required
                                />
                                <Button
                                    @click="rejectPost"
                                    variant="destructive"
                                    class="w-full mt-2"
                                >
                                    <XCircle class="h-4 w-4 mr-2" />
                                    拒绝帖子
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
