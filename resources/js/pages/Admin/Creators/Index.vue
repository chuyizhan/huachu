<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ChevronUp, ChevronDown, Check, X, Star, StarOff, Trash2 } from 'lucide-vue-next';

interface CreatorProfile {
    id: number;
    display_name: string;
    specialty: string;
    verification_status: string;
    verified_at: string | null;
    is_featured: boolean;
    follower_count: number;
    rating: number;
    review_count: number;
}

interface Creator {
    id: number;
    name: string;
    email: string;
    is_creator: boolean;
    is_top_creator: boolean;
    creator_profile: CreatorProfile | null;
    created_at: string;
}

interface PaginatedCreators {
    data: Creator[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    creators: PaginatedCreators;
    filters: {
        search?: string;
        verification_status?: string;
        is_featured?: string;
        is_top_creator?: string;
        sort_by?: string;
        sort_direction?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const verificationStatus = ref(props.filters.verification_status || '');
const isFeatured = ref(props.filters.is_featured || '');
const isTopCreator = ref(props.filters.is_top_creator || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || '20');

const applyFilters = () => {
    router.get('/admin/creators', {
        search: search.value,
        verification_status: verificationStatus.value,
        is_featured: isFeatured.value,
        is_top_creator: isTopCreator.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    verificationStatus.value = '';
    isFeatured.value = '';
    isTopCreator.value = '';
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    perPage.value = '20';
    applyFilters();
};

const toggleSort = (field: string) => {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDirection.value = 'desc';
    }
    applyFilters();
};

const verifyCreator = (id: number) => {
    if (confirm('确定要验证此创作者吗？')) {
        router.post(`/admin/creators/${id}/verify`, {}, {
            preserveScroll: true,
        });
    }
};

const rejectCreator = (id: number) => {
    const notes = prompt('请输入拒绝原因：');
    if (notes) {
        router.post(`/admin/creators/${id}/reject`, {
            verification_notes: notes
        }, {
            preserveScroll: true,
        });
    }
};

const toggleFeatured = (id: number) => {
    router.post(`/admin/creators/${id}/toggle-featured`, {}, {
        preserveScroll: true,
    });
};

const removeCreatorStatus = (id: number) => {
    if (confirm('确定要移除此用户的创作者身份吗？这将软删除其创作者资料。')) {
        router.delete(`/admin/creators/${id}`, {
            preserveScroll: true,
        });
    }
};

const getStatusBadgeClass = (status: string) => {
    const statusMap = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'verified': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return statusMap[status as keyof typeof statusMap] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN');
};
</script>

<template>
    <AppLayout>
        <Head title="创作者管理" />

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">创作者管理</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    管理所有创作者及其资料
                </p>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            搜索
                        </label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="名称、邮箱、专长..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Verification Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            验证状态
                        </label>
                        <select
                            v-model="verificationStatus"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">全部</option>
                            <option value="pending">待审核</option>
                            <option value="verified">已验证</option>
                            <option value="rejected">已拒绝</option>
                        </select>
                    </div>

                    <!-- Is Featured -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            精选状态
                        </label>
                        <select
                            v-model="isFeatured"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">全部</option>
                            <option value="1">精选</option>
                            <option value="0">非精选</option>
                        </select>
                    </div>

                    <!-- Is Top Creator -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            顶级创作者
                        </label>
                        <select
                            v-model="isTopCreator"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">全部</option>
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            每页显示
                        </label>
                        <select
                            v-model="perPage"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="mt-4 flex gap-2">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        应用筛选
                    </button>
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500"
                    >
                        清除筛选
                    </button>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                显示 {{ creators.total }} 个创作者中的 {{ creators.data.length }} 个
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                @click="toggleSort('id')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            >
                                <div class="flex items-center gap-1">
                                    ID
                                    <ChevronUp v-if="sortBy === 'id' && sortDirection === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-if="sortBy === 'id' && sortDirection === 'desc'" class="h-4 w-4" />
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                创作者信息
                            </th>
                            <th
                                @click="toggleSort('follower_count')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            >
                                <div class="flex items-center gap-1">
                                    粉丝数
                                    <ChevronUp v-if="sortBy === 'follower_count' && sortDirection === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-if="sortBy === 'follower_count' && sortDirection === 'desc'" class="h-4 w-4" />
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                验证状态
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                标签
                            </th>
                            <th
                                @click="toggleSort('created_at')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            >
                                <div class="flex items-center gap-1">
                                    创建时间
                                    <ChevronUp v-if="sortBy === 'created_at' && sortDirection === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-if="sortBy === 'created_at' && sortDirection === 'desc'" class="h-4 w-4" />
                                </div>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                操作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="creator in creators.data" :key="creator.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ creator.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ creator.creator_profile?.display_name || creator.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ creator.email }}
                                    </div>
                                    <div v-if="creator.creator_profile?.specialty" class="text-xs text-gray-400">
                                        {{ creator.creator_profile.specialty }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ creator.creator_profile?.follower_count || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="creator.creator_profile"
                                    :class="getStatusBadgeClass(creator.creator_profile.verification_status)"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ creator.creator_profile.verification_status === 'pending' ? '待审核' : '' }}
                                    {{ creator.creator_profile.verification_status === 'verified' ? '已验证' : '' }}
                                    {{ creator.creator_profile.verification_status === 'rejected' ? '已拒绝' : '' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex flex-col gap-1">
                                    <span v-if="creator.is_top_creator" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                        顶级创作者
                                    </span>
                                    <span v-if="creator.creator_profile?.is_featured" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        精选
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(creator.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/creators/${creator.id}`"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        查看
                                    </Link>
                                    <button
                                        v-if="creator.creator_profile?.verification_status === 'pending'"
                                        @click="verifyCreator(creator.id)"
                                        class="text-green-600 hover:text-green-900 dark:text-green-400"
                                        title="验证"
                                    >
                                        <Check class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="creator.creator_profile?.verification_status === 'pending'"
                                        @click="rejectCreator(creator.id)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                                        title="拒绝"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="toggleFeatured(creator.id)"
                                        class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400"
                                        :title="creator.creator_profile?.is_featured ? '取消精选' : '设为精选'"
                                    >
                                        <Star v-if="!creator.creator_profile?.is_featured" class="h-4 w-4" />
                                        <StarOff v-else class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="removeCreatorStatus(creator.id)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                                        title="移除创作者身份"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="creators.links.length > 3" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="creators.links[0].url"
                            :href="creators.links[0].url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            上一页
                        </Link>
                        <Link
                            v-if="creators.links[creators.links.length - 1].url"
                            :href="creators.links[creators.links.length - 1].url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            下一页
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                显示
                                <span class="font-medium">{{ (creators.current_page - 1) * creators.per_page + 1 }}</span>
                                到
                                <span class="font-medium">{{ Math.min(creators.current_page * creators.per_page, creators.total) }}</span>
                                共
                                <span class="font-medium">{{ creators.total }}</span>
                                个结果
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in creators.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                        link.active
                                            ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-300'
                                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === creators.links.length - 1 ? 'rounded-r-md' : '',
                                        !link.url ? 'cursor-not-allowed opacity-50' : ''
                                    ]"
                                    v-html="link.label"
                                >
                                </Link>
                            </nav>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
