<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ChevronUp, ChevronDown } from 'lucide-vue-next';
import { usePagination } from '@/composables/usePagination';

interface User {
    id: number;
    name: string;
    login_name: string;
    email: string;
    is_admin: boolean;
    is_creator: boolean;
    is_verified: boolean;
    is_top_creator: boolean;
    type: number;
    status: number;
    credits: number;
    balance: number;
    followers_count: number;
    following_count: number;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string;
}

interface PaginatedUsers {
    data: User[];
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
    users: PaginatedUsers;
    filters: {
        search?: string;
        is_admin?: string;
        is_creator?: string;
        is_verified?: string;
        type?: string;
        status?: string;
        sort_by?: string;
        sort_direction?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();

const { translatePaginationLabel } = usePagination();

const search = ref(props.filters.search || '');
const isAdmin = ref(props.filters.is_admin || '');
const isCreator = ref(props.filters.is_creator || '');
const isVerified = ref(props.filters.is_verified || '');
const userType = ref(props.filters.type || '');
const userStatus = ref(props.filters.status || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || '20');

const applyFilters = () => {
    router.get('/admin/users', {
        search: search.value,
        is_admin: isAdmin.value,
        is_creator: isCreator.value,
        is_verified: isVerified.value,
        type: userType.value,
        status: userStatus.value,
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
    isAdmin.value = '';
    isCreator.value = '';
    isVerified.value = '';
    userType.value = '';
    userStatus.value = '';
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
        sortDirection.value = 'asc';
    }
    applyFilters();
};

const getStatusText = (status: number) => {
    switch (status) {
        case 1: return '正常';
        case 2: return '暂停';
        case 3: return '封禁';
        default: return '未知';
    }
};

const getTypeText = (type: number) => {
    switch (type) {
        case 1: return '普通';
        case 2: return 'VIP';
        default: return '未知';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="用户管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold">用户管理</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                管理所有用户账户 - 共 {{ users.total }} 位用户
                            </p>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6 space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium mb-2">搜索</label>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="搜索用户名、邮箱或登录名..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">管理员</label>
                                    <select
                                        v-model="isAdmin"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">全部</option>
                                        <option value="1">是</option>
                                        <option value="0">否</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">创作者</label>
                                    <select
                                        v-model="isCreator"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">全部</option>
                                        <option value="1">是</option>
                                        <option value="0">否</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">已验证</label>
                                    <select
                                        v-model="isVerified"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">全部</option>
                                        <option value="1">是</option>
                                        <option value="0">否</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">类型</label>
                                    <select
                                        v-model="userType"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">全部</option>
                                        <option value="1">普通</option>
                                        <option value="2">VIP</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">状态</label>
                                    <select
                                        v-model="userStatus"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">全部</option>
                                        <option value="1">正常</option>
                                        <option value="2">暂停</option>
                                        <option value="3">封禁</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">排序字段</label>
                                    <select
                                        v-model="sortBy"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="id">ID</option>
                                        <option value="name">姓名</option>
                                        <option value="email">邮箱</option>
                                        <option value="login_name">用户名</option>
                                        <option value="credits">积分</option>
                                        <option value="balance">余额</option>
                                        <option value="followers_count">粉丝数</option>
                                        <option value="following_count">关注数</option>
                                        <option value="last_login_at">最后登录时间</option>
                                        <option value="created_at">注册时间</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">排序方向</label>
                                    <select
                                        v-model="sortDirection"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="asc">升序</option>
                                        <option value="desc">降序</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">每页显示</label>
                                    <select
                                        v-model="perPage"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    @click="applyFilters"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                >
                                    搜索
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600"
                                >
                                    清除
                                </button>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th
                                            @click="toggleSort('id')"
                                            class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                        >
                                            ID
                                            <ChevronUp v-if="sortBy === 'id' && sortDirection === 'asc'" class="inline w-4 h-4" />
                                            <ChevronDown v-if="sortBy === 'id' && sortDirection === 'desc'" class="inline w-4 h-4" />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            用户信息
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            角色
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            类型/状态
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            积分/余额
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            粉丝/关注
                                        </th>
                                        <th
                                            @click="toggleSort('last_login_at')"
                                            class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                        >
                                            最后登录
                                            <ChevronUp v-if="sortBy === 'last_login_at' && sortDirection === 'asc'" class="inline w-4 h-4" />
                                            <ChevronDown v-if="sortBy === 'last_login_at' && sortDirection === 'desc'" class="inline w-4 h-4" />
                                        </th>
                                        <th
                                            @click="toggleSort('created_at')"
                                            class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                        >
                                            注册时间
                                            <ChevronUp v-if="sortBy === 'created_at' && sortDirection === 'asc'" class="inline w-4 h-4" />
                                            <ChevronDown v-if="sortBy === 'created_at' && sortDirection === 'desc'" class="inline w-4 h-4" />
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            操作
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            {{ user.id }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <!-- Avatar -->
                                                <img
                                                    v-if="user.avatar"
                                                    :src="user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`"
                                                    :alt="user.name"
                                                    class="w-10 h-10 rounded-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold"
                                                >
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <!-- User Info -->
                                                <div class="flex flex-col gap-1">
                                                    <div class="font-medium">{{ user.name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">@{{ user.login_name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1">
                                                <span v-if="user.is_admin" class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 w-fit">
                                                    管理员
                                                </span>
                                                <span v-if="user.is_creator" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 w-fit">
                                                    创作者
                                                </span>
                                                <span v-if="user.is_top_creator" class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10 w-fit">
                                                    顶级创作者
                                                </span>
                                                <span v-if="user.is_verified" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/10 w-fit">
                                                    已验证
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1 text-sm">
                                                <div>{{ getTypeText(user.type) }}</div>
                                                <div>{{ getStatusText(user.status) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex flex-col gap-1 text-sm">
                                                <div>💰 {{ user.credits }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">余额: {{ user.balance }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col gap-1 text-sm">
                                                <div>👥 {{ user.followers_count }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">关注: {{ user.following_count }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1 text-sm">
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    {{ user.last_login_at ? formatDate(user.last_login_at) : '从未登录' }}
                                                </div>
                                                <div v-if="user.last_login_ip" class="text-xs text-gray-500 dark:text-gray-400">
                                                    IP: {{ user.last_login_ip }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-4">
                                                <Link
                                                    :href="`/admin/users/${user.id}`"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    查看
                                                </Link>
                                                <Link
                                                    :href="`/admin/users/${user.id}/edit`"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    编辑
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.last_page > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                显示 {{ users.data.length }} 条，共 {{ users.total }} 条
                            </div>
                            <nav class="flex gap-2">
                                <Link
                                    v-for="link in users.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded-md px-3 py-2 text-sm font-medium',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url && 'cursor-not-allowed opacity-50',
                                    ]"
                                    v-html="translatePaginationLabel(link.label)"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
