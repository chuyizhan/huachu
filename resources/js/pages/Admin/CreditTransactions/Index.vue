<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Plus } from 'lucide-vue-next'

interface User {
    id: number
    name: string
    login_name: string
    email: string
}

interface CreditTransaction {
    id: number
    user: User
    type: string
    credits: number
    reason: string
    metadata: any
    related_type: string | null
    related_id: number | null
    created_at: string
}

interface Props {
    transactions: {
        data: CreditTransaction[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    users: User[]
    reasons: string[]
    stats: {
        total_transactions: number
        total_earned: number
        total_spent: number
        total_deducted: number
        total_refunded: number
    }
    filters: {
        search?: string
        user_id?: string
        type?: string
        reason?: string
        credits_min?: string
        credits_max?: string
        date_from?: string
        date_to?: string
        related_type?: string
        sort_by?: string
        sort_direction?: string
        per_page?: number
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const userId = ref(props.filters.user_id || '')
const type = ref(props.filters.type || '')
const reason = ref(props.filters.reason || '')
const creditsMin = ref(props.filters.credits_min || '')
const creditsMax = ref(props.filters.credits_max || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const relatedType = ref(props.filters.related_type || '')
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortDirection = ref(props.filters.sort_direction || 'desc')
const perPage = ref(props.filters.per_page || 20)

const applyFilters = () => {
    router.get(
        '/admin/credit-transactions',
        {
            search: search.value || undefined,
            user_id: userId.value || undefined,
            type: type.value || undefined,
            reason: reason.value || undefined,
            credits_min: creditsMin.value || undefined,
            credits_max: creditsMax.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            related_type: relatedType.value || undefined,
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
    type.value = ''
    reason.value = ''
    creditsMin.value = ''
    creditsMax.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    relatedType.value = ''
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
        '/admin/credit-transactions',
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

const deleteTransaction = (id: number) => {
    if (confirm('确定要删除这个金币交易吗？这将回滚用户金币。')) {
        router.delete(`/admin/credit-transactions/${id}`, {
            preserveScroll: true,
        })
    }
}

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        earned: 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-200',
        spent: 'text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-200',
        deducted: 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-200',
        refunded: 'text-purple-800 bg-purple-100 dark:bg-purple-900 dark:text-purple-200',
    }
    return colors[type] || 'text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-200'
}

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        earned: '获得',
        spent: '消费',
        deducted: '扣除',
        refunded: '退款',
    }
    return labels[type] || type
}
</script>

<template>
    <AppLayout>
        <Head title="金币交易管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-semibold">金币交易管理</h1>
                            <Link href="/admin/credit-transactions/create">
                                <Button>
                                    <Plus class="mr-2 h-4 w-4" />
                                    创建交易
                                </Button>
                            </Link>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid gap-4 md:grid-cols-4 mb-6">
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardDescription>总交易数</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.total_transactions }}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardDescription>总获得</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-green-600">+{{ stats.total_earned }}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardDescription>总消费</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-blue-600">{{ stats.total_spent }}</div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardDescription>总扣除</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-red-600">{{ stats.total_deducted }}</div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6 space-y-4">
                            <div class="flex flex-wrap gap-4">
                                <!-- Search -->
                                <div class="flex-1 min-w-[200px]">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="搜索原因、用户名、邮箱..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>

                                <!-- User Filter -->
                                <div class="min-w-[200px]">
                                    <select
                                        v-model="userId"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部用户</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} (@{{ user.login_name }})
                                        </option>
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
                                        <option value="earned">获得</option>
                                        <option value="spent">消费</option>
                                        <option value="deducted">扣除</option>
                                    </select>
                                </div>

                                <!-- Reason Filter -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="reason"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部原因</option>
                                        <option v-for="r in reasons" :key="r" :value="r">
                                            {{ r }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <!-- Credits Min -->
                                <div class="min-w-[120px]">
                                    <input
                                        v-model="creditsMin"
                                        type="number"
                                        placeholder="最小金币"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
                                </div>

                                <!-- Credits Max -->
                                <div class="min-w-[120px]">
                                    <input
                                        v-model="creditsMax"
                                        type="number"
                                        placeholder="最大金币"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
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

                                <!-- Related Type -->
                                <div class="min-w-[150px]">
                                    <select
                                        v-model="relatedType"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">全部关联</option>
                                        <option value="App\Models\Post">帖子</option>
                                        <option value="App\Models\PostPurchase">购买</option>
                                    </select>
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
                                        <option value="user_id">用户</option>
                                        <option value="type">类型</option>
                                        <option value="credits">金币</option>
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

                        <!-- Transactions Table -->
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
                                            @click="toggleSort('user_id')"
                                        >
                                            用户 {{ getSortIcon('user_id') }}
                                        </TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('type')"
                                        >
                                            类型 {{ getSortIcon('type') }}
                                        </TableHead>
                                        <TableHead
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="toggleSort('credits')"
                                        >
                                            金币 {{ getSortIcon('credits') }}
                                        </TableHead>
                                        <TableHead>原因</TableHead>
                                        <TableHead>关联</TableHead>
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
                                        v-for="transaction in transactions.data"
                                        :key="transaction.id"
                                    >
                                        <TableCell class="font-medium">
                                            {{ transaction.id }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-1">
                                                <div class="font-medium">{{ transaction.user.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    @{{ transaction.user.login_name }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="getTypeColor(transaction.type)"
                                            >
                                                {{ getTypeLabel(transaction.type) }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                class="font-semibold"
                                                :class="{
                                                    'text-green-600': transaction.credits > 0,
                                                    'text-red-600': transaction.credits < 0,
                                                }"
                                            >
                                                {{ transaction.credits > 0 ? '+' : '' }}{{ transaction.credits }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            {{ transaction.reason }}
                                        </TableCell>
                                        <TableCell>
                                            <div v-if="transaction.related_type" class="text-sm">
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    {{ transaction.related_type.split('\\').pop() }}
                                                </div>
                                                <div>ID: {{ transaction.related_id }}</div>
                                            </div>
                                            <span v-else class="text-gray-400">-</span>
                                        </TableCell>
                                        <TableCell class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(transaction.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            <button
                                                type="button"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                @click="deleteTransaction(transaction.id)"
                                            >
                                                删除
                                            </button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                显示 {{ transactions.data.length }} 条，共 {{ transactions.total }} 条
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="transactions.current_page === 1"
                                    @click="goToPage(transactions.current_page - 1)"
                                >
                                    上一页
                                </button>
                                <span class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    第 {{ transactions.current_page }} / {{ transactions.last_page }} 页
                                </span>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :disabled="transactions.current_page === transactions.last_page"
                                    @click="goToPage(transactions.current_page + 1)"
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
