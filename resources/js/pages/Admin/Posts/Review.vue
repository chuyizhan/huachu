<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { CheckCircle, XCircle, Clock, Eye, User, Calendar } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    slug: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    category: {
        id: number;
        name: string;
    };
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
    first_image?: string;
    images_count: number;
    videos_count: number;
}

interface Props {
    posts: {
        data: Post[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        pending: number;
        approved: number;
        rejected: number;
    };
    currentStatus: string;
}

const props = defineProps<Props>();
const page = usePage();

const selectedPosts = ref<number[]>([]);

function changeStatus(status: string) {
    router.get(route('admin.post-reviews.index'), { status }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function viewPost(postId: number) {
    router.get(route('admin.post-reviews.show', postId));
}

function approvePost(postId: number) {
    if (confirm('确认批准这个帖子？')) {
        router.post(route('admin.post-reviews.approve', postId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                selectedPosts.value = selectedPosts.value.filter(id => id !== postId);
            },
        });
    }
}

function rejectPost(postId: number) {
    const notes = prompt('请输入拒绝原因：');
    if (notes) {
        router.post(route('admin.post-reviews.reject', postId), { notes }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedPosts.value = selectedPosts.value.filter(id => id !== postId);
            },
        });
    }
}

function batchApprove() {
    if (selectedPosts.value.length === 0) {
        alert('请选择要批准的帖子');
        return;
    }

    if (confirm(`确认批准 ${selectedPosts.value.length} 个帖子？`)) {
        router.post(route('admin.post-reviews.batch-approve'), {
            post_ids: selectedPosts.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedPosts.value = [];
            },
        });
    }
}

function toggleSelection(postId: number) {
    const index = selectedPosts.value.indexOf(postId);
    if (index > -1) {
        selectedPosts.value.splice(index, 1);
    } else {
        selectedPosts.value.push(postId);
    }
}

function toggleAll() {
    if (selectedPosts.value.length === props.posts.data.length) {
        selectedPosts.value = [];
    } else {
        selectedPosts.value = props.posts.data.map(p => p.id);
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
</script>

<template>
    <Head title="帖子审核管理" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-white">帖子审核管理</h1>
                <p class="text-[#999999] mt-2">审核和管理用户提交的帖子</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white">待审核</CardTitle>
                        <Clock class="h-4 w-4 text-yellow-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-white">{{ stats.pending }}</div>
                    </CardContent>
                </Card>

                <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white">已批准</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-white">{{ stats.approved }}</div>
                    </CardContent>
                </Card>

                <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white">已拒绝</CardTitle>
                        <XCircle class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-white">{{ stats.rejected }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs and Actions -->
            <Card class="bg-[#1c1c1c] border-[#2a2a2a]">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <Tabs :model-value="currentStatus" class="w-full">
                            <TabsList class="bg-[#2a2a2a]">
                                <TabsTrigger value="pending" @click="changeStatus('pending')">
                                    待审核 ({{ stats.pending }})
                                </TabsTrigger>
                                <TabsTrigger value="approved" @click="changeStatus('approved')">
                                    已批准 ({{ stats.approved }})
                                </TabsTrigger>
                                <TabsTrigger value="rejected" @click="changeStatus('rejected')">
                                    已拒绝 ({{ stats.rejected }})
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>

                        <Button
                            v-if="currentStatus === 'pending' && selectedPosts.length > 0"
                            @click="batchApprove"
                            class="ml-4 bg-green-600 hover:bg-green-700"
                        >
                            批量批准 ({{ selectedPosts.length }})
                        </Button>
                    </div>
                </CardHeader>

                <CardContent>
                    <!-- Posts Table -->
                    <div v-if="posts.data.length > 0" class="rounded-md border border-[#2a2a2a]">
                        <Table>
                            <TableHeader>
                                <TableRow class="border-[#2a2a2a] hover:bg-[#2a2a2a]/50">
                                    <TableHead v-if="currentStatus === 'pending'" class="w-12">
                                        <input
                                            type="checkbox"
                                            :checked="selectedPosts.length === posts.data.length"
                                            @change="toggleAll"
                                            class="rounded border-gray-300"
                                        />
                                    </TableHead>
                                    <TableHead class="text-white">帖子</TableHead>
                                    <TableHead class="text-white">作者</TableHead>
                                    <TableHead class="text-white">分类</TableHead>
                                    <TableHead class="text-white">媒体</TableHead>
                                    <TableHead class="text-white">状态</TableHead>
                                    <TableHead class="text-white">审核信息</TableHead>
                                    <TableHead class="text-white">操作</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="post in posts.data"
                                    :key="post.id"
                                    class="border-[#2a2a2a] hover:bg-[#2a2a2a]/50"
                                >
                                    <TableCell v-if="currentStatus === 'pending'">
                                        <input
                                            type="checkbox"
                                            :checked="selectedPosts.includes(post.id)"
                                            @change="toggleSelection(post.id)"
                                            class="rounded border-gray-300"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <img
                                                v-if="post.first_image"
                                                :src="post.first_image"
                                                alt=""
                                                class="w-16 h-16 object-cover rounded"
                                            />
                                            <div>
                                                <div class="font-medium text-white">{{ post.title }}</div>
                                                <div class="text-xs text-[#999999]">
                                                    {{ formatDate(post.created_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-white">{{ post.user.name }}</div>
                                        <div class="text-xs text-[#999999]">{{ post.user.email }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="text-white border-[#2a2a2a]">
                                            {{ post.category.name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-[#999999]">
                                            {{ post.images_count }} 图片
                                            <br />
                                            {{ post.videos_count }} 视频
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getStatusColor(post.review_status)">
                                            {{ post.review_status === 'pending' ? '待审核' :
                                               post.review_status === 'approved' ? '已批准' : '已拒绝' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="post.reviewer" class="text-sm">
                                            <div class="text-white">{{ post.reviewer.name }}</div>
                                            <div class="text-xs text-[#999999]">
                                                {{ formatDate(post.reviewed_at) }}
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-[#999999]">-</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="viewPost(post.id)"
                                                class="border-[#2a2a2a] text-white hover:bg-[#2a2a2a]"
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                v-if="post.review_status === 'pending'"
                                                size="sm"
                                                @click="approvePost(post.id)"
                                                class="bg-green-600 hover:bg-green-700"
                                            >
                                                <CheckCircle class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                v-if="post.review_status === 'pending'"
                                                size="sm"
                                                variant="destructive"
                                                @click="rejectPost(post.id)"
                                            >
                                                <XCircle class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <p class="text-[#999999]">没有找到帖子</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="posts.last_page > 1" class="flex justify-center gap-2 mt-4">
                        <Button
                            v-for="page in posts.last_page"
                            :key="page"
                            size="sm"
                            :variant="page === posts.current_page ? 'default' : 'outline'"
                            @click="router.get(route('admin.post-reviews.index'), { status: currentStatus, page })"
                            class="border-[#2a2a2a]"
                        >
                            {{ page }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
