<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

interface Category {
    id: number
    name: string
    slug: string
    description: string | null
    color: string
    icon: string | null
    sort_order: number
    is_active: boolean
    is_nav_item: boolean
    nav_route: string | null
    posts_count: number
    created_at: string
    updated_at: string
}

interface Props {
    categories: {
        data: Category[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    filters: {
        search?: string
        is_active?: string
        is_nav_item?: string
        sort_by?: string
        sort_direction?: string
        per_page?: number
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const isActive = ref(props.filters.is_active || '')
const isNavItem = ref(props.filters.is_nav_item || '')
const sortBy = ref(props.filters.sort_by || 'sort_order')
const sortDirection = ref(props.filters.sort_direction || 'asc')
const perPage = ref(props.filters.per_page || 20)

const applyFilters = () => {
    router.get(
        '/admin/categories',
        {
            search: search.value || undefined,
            is_active: isActive.value || undefined,
            is_nav_item: isNavItem.value || undefined,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const resetFilters = () => {
    search.value = ''
    isActive.value = ''
    isNavItem.value = ''
    sortBy.value = 'sort_order'
    sortDirection.value = 'asc'
    perPage.value = 20
    applyFilters()
}

const toggleSort = (column: string) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = column
        sortDirection.value = 'asc'
    }
    applyFilters()
}

const getSortIcon = (column: string) => {
    if (sortBy.value !== column) return '↕'
    return sortDirection.value === 'asc' ? '↑' : '↓'
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const goToPage = (page: number) => {
    router.get(
        '/admin/categories',
        {
            ...props.filters,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const deleteCategory = (id: number) => {
    if (confirm('确定要删除这个分类吗？此操作无法撤销。')) {
        router.delete(`/admin/categories/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be shown via Laravel flash message
            },
        })
    }
}
</script>

<template>
    <AppLayout>
        <Head title="分类管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-semibold">分类管理</h1>
                            <Link href="/admin/categories/create">
                                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    创建分类
                                </button>
                            </Link>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6 space-y-4">
                            <div class="flex flex-wrap gap-4">
                                <!-- Search -->
                                <div class="flex-1 min-w-[200px]">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="搜索分类名称、别名或描述..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>

                                <!-- Is Active Filter -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="isActive"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部状态</option>
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </div>

                                <!-- Is Nav Item Filter -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="isNavItem"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部导航</option>
                                        <option value="1">导航项</option>
                                        <option value="0">非导航项</option>
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="sortBy"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="id">ID</option>
                                        <option value="name">分类名称</option>
                                        <option value="slug">别名</option>
                                        <option value="sort_order">排序</option>
                                        <option value="posts_count">帖子数量</option>
                                        <option value="created_at">创建时间</option>
                                    </select>
                                </div>

                                <!-- Sort Direction -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="sortDirection"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="asc">升序</option>
                                        <option value="desc">降序</option>
                                    </select>
                                </div>

                                <!-- Per Page -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="perPage"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option :value="10">10 / 页</option>
                                        <option :value="20">20 / 页</option>
                                        <option :value="50">50 / 页</option>
                                        <option :value="100">100 / 页</option>
                                        <option :value="200">200 / 页</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    @click="applyFilters"
                                >
                                    搜索
                                </button>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                    @click="resetFilters"
                                >
                                    重置
                                </button>
                            </div>
                        </div>

                        <!-- Categories Table -->
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('id')"
                                        >
                                            ID {{ getSortIcon('id') }}
                                        </TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('name')"
                                        >
                                            分类信息 {{ getSortIcon('name') }}
                                        </TableHead>
                                        <TableHead>颜色/图标</TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('sort_order')"
                                        >
                                            排序 {{ getSortIcon('sort_order') }}
                                        </TableHead>
                                        <TableHead>状态</TableHead>
                                        <TableHead>导航</TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('posts_count')"
                                        >
                                            帖子数量 {{ getSortIcon('posts_count') }}
                                        </TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('created_at')"
                                        >
                                            创建时间 {{ getSortIcon('created_at') }}
                                        </TableHead>
                                        <TableHead>操作</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="category in categories.data"
                                        :key="category.id"
                                    >
                                        <TableCell class="font-medium">
                                            {{ category.id }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1">
                                                <div class="font-medium">{{ category.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ category.slug }}
                                                </div>
                                                <div
                                                    v-if="category.description"
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ category.description }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded"
                                                    :style="{ backgroundColor: category.color }"
                                                />
                                                <span
                                                    v-if="category.icon"
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >{{ category.icon }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {{ category.sort_order }}
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                v-if="category.is_active"
                                                class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200"
                                            >
                                                启用
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-200"
                                            >
                                                禁用
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                v-if="category.is_nav_item"
                                                class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200"
                                            >
                                                是
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-200"
                                            >
                                                否
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            {{ category.posts_count }}
                                        </TableCell>
                                        <TableCell class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(category.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/admin/categories/${category.id}/edit`"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    编辑
                                                </Link>
                                                <button
                                                    v-if="category.posts_count === 0"
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    @click="deleteCategory(category.id)"
                                                >
                                                    删除
                                                </button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                显示 {{ categories.data.length }} 条，共 {{ categories.total }} 条
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="categories.current_page === 1"
                                    @click="goToPage(categories.current_page - 1)"
                                >
                                    上一页
                                </button>
                                <span class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    第 {{ categories.current_page }} / {{ categories.last_page }} 页
                                </span>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="categories.current_page === categories.last_page"
                                    @click="goToPage(categories.current_page + 1)"
                                >
                                    下一页
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
