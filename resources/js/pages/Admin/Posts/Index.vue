<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

interface User {
    id: number
    name: string
    login_name: string
}

interface Category {
    id: number
    name: string
    slug: string
}

interface Post {
    id: number
    title: string
    slug: string
    excerpt: string | null
    user: User
    category: Category
    type: string
    status: string
    is_featured: boolean
    is_premium: boolean
    price: number | null
    free_after: string | null
    view_count: number
    like_count: number
    likes_count: number
    purchases_count: number
    published_at: string | null
    created_at: string
    updated_at: string
}

interface Props {
    posts: {
        data: Post[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    categories: Category[]
    users: User[]
    filters: {
        search?: string
        user_id?: string
        category_id?: string
        status?: string
        type?: string
        is_featured?: string
        is_premium?: string
        price_filter?: string
        is_published?: string
        date_from?: string
        date_to?: string
        sort_by?: string
        sort_direction?: string
        per_page?: number
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const userId = ref(props.filters.user_id || '')
const categoryId = ref(props.filters.category_id || '')
const status = ref(props.filters.status || '')
const type = ref(props.filters.type || '')
const isFeatured = ref(props.filters.is_featured || '')
const isPremium = ref(props.filters.is_premium || '')
const priceFilter = ref(props.filters.price_filter || '')
const isPublished = ref(props.filters.is_published || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortDirection = ref(props.filters.sort_direction || 'desc')
const perPage = ref(props.filters.per_page || 20)

const applyFilters = () => {
    router.get(
        '/admin/posts',
        {
            search: search.value || undefined,
            user_id: userId.value || undefined,
            category_id: categoryId.value || undefined,
            status: status.value || undefined,
            type: type.value || undefined,
            is_featured: isFeatured.value || undefined,
            is_premium: isPremium.value || undefined,
            price_filter: priceFilter.value || undefined,
            is_published: isPublished.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
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
    userId.value = ''
    categoryId.value = ''
    status.value = ''
    type.value = ''
    isFeatured.value = ''
    isPremium.value = ''
    priceFilter.value = ''
    isPublished.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    sortBy.value = 'created_at'
    sortDirection.value = 'desc'
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

const formatDate = (dateString: string | null) => {
    if (!dateString) return '未发布'
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
        '/admin/posts',
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

const deletePost = (id: number) => {
    if (confirm('确定要删除这个帖子吗？此操作无法撤销。')) {
        router.delete(`/admin/posts/${id}`, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <AppLayout>
        <Head title="帖子管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-semibold">帖子管理</h1>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6 space-y-4">
                            <div class="flex flex-wrap gap-4">
                                <!-- Search -->
                                <div class="flex-1 min-w-[200px]">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="搜索标题、别名、摘要或内容..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>

                                <!-- User Filter -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="userId"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部作者</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} (@{{ user.login_name }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Category Filter -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="categoryId"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部分类</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部状态</option>
                                        <option value="draft">草稿</option>
                                        <option value="published">已发布</option>
                                        <option value="archived">已归档</option>
                                    </select>
                                </div>

                                <!-- Type Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部类型</option>
                                        <option value="article">文章</option>
                                        <option value="video">视频</option>
                                        <option value="image">图片</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <!-- Featured Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="isFeatured"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部精选</option>
                                        <option value="1">精选</option>
                                        <option value="0">非精选</option>
                                    </select>
                                </div>

                                <!-- Premium Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="isPremium"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部会员</option>
                                        <option value="1">会员专享</option>
                                        <option value="0">普通</option>
                                    </select>
                                </div>

                                <!-- Price Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="priceFilter"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部价格</option>
                                        <option value="free">免费</option>
                                        <option value="paid">付费</option>
                                    </select>
                                </div>

                                <!-- Published Filter -->
                                <div class="min-w-[120px]">
                                    <select
                                        v-model="isPublished"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">发布状态</option>
                                        <option value="1">已发布</option>
                                        <option value="0">未发布</option>
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="min-w-[150px]">
                                    <input
                                        v-model="dateFrom"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
                                </div>

                                <!-- Date To -->
                                <div class="min-w-[150px]">
                                    <input
                                        v-model="dateTo"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <!-- Sort By -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="sortBy"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="id">ID</option>
                                        <option value="title">标题</option>
                                        <option value="view_count">浏览量</option>
                                        <option value="like_count">点赞数</option>
                                        <option value="published_at">发布时间</option>
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

                        <!-- Posts Table -->
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
                                            @click="toggleSort('title')"
                                        >
                                            帖子信息 {{ getSortIcon('title') }}
                                        </TableHead>
                                        <TableHead>作者</TableHead>
                                        <TableHead>分类</TableHead>
                                        <TableHead>状态/类型</TableHead>
                                        <TableHead>标签</TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('view_count')"
                                        >
                                            统计 {{ getSortIcon('view_count') }}
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
                                        v-for="post in posts.data"
                                        :key="post.id"
                                    >
                                        <TableCell class="font-medium">
                                            {{ post.id }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1 max-w-md">
                                                <div class="font-medium">{{ post.title }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ post.slug }}
                                                </div>
                                                <div
                                                    v-if="post.excerpt"
                                                    class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2"
                                                >
                                                    {{ post.excerpt }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1">
                                                <div class="font-medium">{{ post.user.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    @{{ post.user.login_name }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ post.category.name }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1">
                                                <div class="text-sm">{{ post.status }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ post.type }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col gap-1">
                                                <span
                                                    v-if="post.is_featured"
                                                    class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200 w-fit"
                                                >
                                                    精选
                                                </span>
                                                <span
                                                    v-if="post.is_premium"
                                                    class="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full dark:bg-purple-900 dark:text-purple-200 w-fit"
                                                >
                                                    会员
                                                </span>
                                                <span
                                                    v-if="post.price && post.price > 0"
                                                    class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200 w-fit"
                                                >
                                                    ¥{{ post.price }}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm space-y-1">
                                                <div>👁️ {{ post.view_count }}</div>
                                                <div>❤️ {{ post.likes_count }}</div>
                                                <div>💰 {{ post.purchases_count }}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(post.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/admin/posts/${post.id}/edit`"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    编辑
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    @click="deletePost(post.id)"
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
                                显示 {{ posts.data.length }} 条，共 {{ posts.total }} 条
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="posts.current_page === 1"
                                    @click="goToPage(posts.current_page - 1)"
                                >
                                    上一页
                                </button>
                                <span class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    第 {{ posts.current_page }} / {{ posts.last_page }} 页
                                </span>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="posts.current_page === posts.last_page"
                                    @click="goToPage(posts.current_page + 1)"
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
