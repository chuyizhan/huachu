<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { CheckCircle, XCircle, Clock, Eye } from 'lucide-vue-next';

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
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="mb-6">
                            <h1 class="text-2xl font-semibold">帖子审核管理</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">审核和管理用户提交的帖子</p>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">待审核</p>
                                        <p class="text-2xl font-bold mt-1">{{ stats.pending }}</p>
                                    </div>
                                    <Clock class="h-8 w-8 text-yellow-500" />
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">已批准</p>
                                        <p class="text-2xl font-bold mt-1">{{ stats.approved }}</p>
                                    </div>
                                    <CheckCircle class="h-8 w-8 text-green-500" />
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">已拒绝</p>
                                        <p class="text-2xl font-bold mt-1">{{ stats.rejected }}</p>
                                    </div>
                                    <XCircle class="h-8 w-8 text-red-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Tabs and Actions -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="border-b border-gray-200 dark:border-gray-700">
                                    <nav class="-mb-px flex space-x-8">
                                        <button
                                            @click="changeStatus('pending')"
                                            :class="[
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                                currentStatus === 'pending'
                                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                            ]"
                                        >
                                            待审核 ({{ stats.pending }})
                                        </button>
                                        <button
                                            @click="changeStatus('approved')"
                                            :class="[
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                                currentStatus === 'approved'
                                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                            ]"
                                        >
                                            已批准 ({{ stats.approved }})
                                        </button>
                                        <button
                                            @click="changeStatus('rejected')"
                                            :class="[
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                                currentStatus === 'rejected'
                                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                                            ]"
                                        >
                                            已拒绝 ({{ stats.rejected }})
                                        </button>
                                    </nav>
                                </div>

                                <Button
                                    v-if="currentStatus === 'pending' && selectedPosts.length > 0"
                                    @click="batchApprove"
                                    class="bg-green-600 hover:bg-green-700 text-white"
                                >
                                    批量批准 ({{ selectedPosts.length }})
                                </Button>
                            </div>
                        </div>

                        <!-- Posts Table -->
                        <!-- Posts Table -->
                        <div v-if="posts.data.length > 0" class="overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
                            <Table>
                                <TableHeader>
                                    <TableRow class="bg-gray-50 dark:bg-gray-700">
                                        <TableHead v-if="currentStatus === 'pending'" class="w-12">
                                            <input
                                                type="checkbox"
                                                :checked="selectedPosts.length === posts.data.length"
                                                @change="toggleAll"
                                                class="rounded border-gray-300 dark:border-gray-600"
                                            />
                                        </TableHead>
                                        <TableHead>帖子</TableHead>
                                        <TableHead>作者</TableHead>
                                        <TableHead>分类</TableHead>
                                        <TableHead>媒体</TableHead>
                                        <TableHead>状态</TableHead>
                                        <TableHead>审核信息</TableHead>
                                        <TableHead>操作</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="post in posts.data"
                                        :key="post.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <TableCell v-if="currentStatus === 'pending'">
                                            <input
                                                type="checkbox"
                                                :checked="selectedPosts.includes(post.id)"
                                                @change="toggleSelection(post.id)"
                                                class="rounded border-gray-300 dark:border-gray-600"
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
                                                    <div class="font-medium">{{ post.title }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ formatDate(post.created_at) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div>{{ post.user.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ post.user.email }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {{ post.category.name }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
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
                                                <div>{{ post.reviewer.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ formatDate(post.reviewed_at) }}
                                                </div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="viewPost(post.id)"
                                                >
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    v-if="post.review_status === 'pending'"
                                                    size="sm"
                                                    @click="approvePost(post.id)"
                                                    class="bg-green-600 hover:bg-green-700 text-white"
                                                >
                                                    <CheckCircle class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    v-if="post.review_status === 'pending'"
                                                    size="sm"
                                                    @click="rejectPost(post.id)"
                                                    class="bg-red-600 hover:bg-red-700 text-white"
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
                        <div v-else class="text-center py-12 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-gray-500 dark:text-gray-400">没有找到帖子</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="posts.last_page > 1" class="flex justify-center gap-2 mt-6">
                            <Button
                                v-for="page in posts.last_page"
                                :key="page"
                                size="sm"
                                :variant="page === posts.current_page ? 'default' : 'outline'"
                                @click="router.get(route('admin.post-reviews.index'), { status: currentStatus, page })"
                            >
                                {{ page }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
